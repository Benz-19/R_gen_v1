"""
Smart Reconciliation Engine
============================

A robust, data-driven reconciliation engine for CSV, XLSX/XLS and text-based
PDF transaction statements.

Implemented pipelines:
1. Automatic file/table/column detection
2. Fuzzy + semantic-ish transaction matching
3. Dynamic FX rate estimation
4. Split-payment clustering
5. Gateway fee deduction inference
6. Confidence scoring and explainable match reasons
7. One-to-one matching (a source transaction is not reused accidentally)
8. CSV / Excel / PDF ingestion
9. Optional OCR fallback for scanned PDFs when pytesseract is installed
10. JSON-friendly and DataFrame-friendly results
11. Post-match exception audit (two-way identity join, categorized exceptions)
12. Exception queue feeds the existing report structure (unmatched_*_rows)
"""

from __future__ import annotations

import argparse
import json
import math
import os
import re
import sys
import unicodedata
import warnings
from dataclasses import dataclass, asdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Dict, Iterable, List, Optional, Sequence, Tuple, Union

import pandas as pd

# Fallback handling for column classifier dependency
try:
    from utils.column_classifier import SelfLearningColumnClassifier
except ImportError:
    SelfLearningColumnClassifier = None

try:
    import pdfplumber
except ImportError:
    pdfplumber = None

try:
    from rapidfuzz import fuzz
except ImportError:
    # Fallback so the engine still imports if rapidfuzz is absent.
    from difflib import SequenceMatcher

    class _FuzzFallback:
        @staticmethod
        def ratio(a: str, b: str) -> float:
            return SequenceMatcher(None, a, b).ratio() * 100.0

        @staticmethod
        def token_set_ratio(a: str, b: str) -> float:
            aa = set(str(a).lower().split())
            bb = set(str(b).lower().split())
            if not aa and not bb:
                return 100.0
            if not aa or not bb:
                return 0.0
            common = " ".join(sorted(aa & bb))
            left = " ".join(sorted(aa))
            right = " ".join(sorted(bb))
            scores = [
                SequenceMatcher(None, left, right).ratio(),
                SequenceMatcher(None, common, left).ratio(),
                SequenceMatcher(None, common, right).ratio(),
            ]
            return max(scores) * 100.0

    fuzz = _FuzzFallback()


@dataclass
class EngineConfig:
    # Amount comparison tolerance in the SAME currency.
    amount_tolerance: float = 0.01

    # Maximum date difference for normal matching.
    date_window_days: int = 3

    # Minimum overall score for an automatic match.
    match_threshold: float = 82.0

    # Minimum score below which a candidate is considered unrelated.
    candidate_threshold: float = 45.0

    # Minimum similarity for a useful description/reference comparison.
    fuzzy_threshold: float = 55.0

    # Maximum number of rows used when looking for split payments.
    max_split_items: int = 5

    # Date window for split payments.
    split_window_days: int = 5

    # Fee inference tolerance.
    fee_tolerance: float = 0.02

    # Maximum reasonable gateway fee percentage considered during inference.
    max_reasonable_fee_percent: float = 0.20  # 20%

    # Minimum amount needed before trying a percentage-based fee inference.
    min_amount_for_fee_inference: float = 1.0

    # FX sanity range. Extreme values are rejected.
    min_fx_rate: float = 0.000001
    max_fx_rate: float = 1000000.0

    # Maximum number of candidate rows examined for each transaction.
    max_candidates_per_row: int = 150

    # Dashboard UI / Laravel CLI Configuration Overrides
    start_date: Optional[str] = None
    end_date: Optional[str] = None
    variance_tolerance: float = 0.00
    settlement_buffer: int = 2

    # Active ML Pipeline Toggles
    enable_fuzzy: bool = True
    enable_fx: bool = True
    enable_split: bool = True
    enable_gateway: bool = True

    # ------------------------------------------------------------------
    # Exception audit settings
    # ------------------------------------------------------------------
    # Hard-wire the real column names here if auto-detection guesses wrong.
    audit_entity_column: Optional[str] = None
    audit_direction_column: Optional[str] = None
    # Regex whose LAST numeric group (group 1 if present) is the trace ID.
    # Default: last run of digits in the reference.
    audit_trace_pattern: Optional[str] = None

    # When True, the categorized exceptions REPLACE the engine's raw
    # unmatched lists in unmatched_a_rows / unmatched_b_rows / summary, so the
    # browser report shows every discrepancy. Set False to restore the old
    # report exactly (exceptions are then only in result["exceptions"]).
    report_exceptions_as_unmatched: bool = True


def safe_text(value: Any) -> str:
    """Convert arbitrary values to clean text without producing 'nan'."""
    if value is None:
        return ""
    try:
        if pd.isna(value):
            return ""
    except Exception:
        pass
    return str(value).strip()


def normalize_text(value: Any) -> str:
    """
    Normalize transaction text for comparison.
    Removes accents, punctuation, and excessive whitespace.
    """
    text = safe_text(value)
    text = unicodedata.normalize("NFKD", text)
    text = "".join(ch for ch in text if not unicodedata.combining(ch))
    text = text.lower()

    # Replace common separators with spaces.
    text = re.sub(r"[_/\\|:;,\-]+", " ", text)
    # Keep letters/numbers/spaces.
    text = re.sub(r"[^a-z0-9\s]", " ", text)
    # Collapse whitespace.
    text = re.sub(r"\s+", " ", text).strip()
    return text


def normalized_column_name(value: Any) -> str:
    """Normalize a column name for internal semantic analysis."""
    return normalize_text(value)


def numeric_parse(value: Any) -> Optional[float]:
    """Parse many common financial number formats."""
    if value is None or isinstance(value, bool):
        return None

    if isinstance(value, (int, float)):
        if isinstance(value, float) and math.isnan(value):
            return None
        return float(value)

    text = safe_text(value)
    if not text:
        return None

    negative = False
    if text.startswith("(") and text.endswith(")"):
        negative = True
        text = text[1:-1]

    text = text.replace("\u00a0", " ")
    text = re.sub(r"[^\d,\.\-+]", "", text)

    if not text or text in {"-", "+", ".", ","}:
        return None

    if "," in text and "." in text:
        if text.rfind(",") > text.rfind("."):
            text = text.replace(".", "").replace(",", ".")
        else:
            text = text.replace(",", "")
    elif "," in text:
        parts = text.split(",")
        if len(parts) == 2 and len(parts[1]) in (1, 2):
            text = text.replace(",", ".")
        else:
            text = text.replace(",", "")
    elif text.count(".") > 1:
        parts = text.split(".")
        if len(parts[-1]) in (1, 2):
            text = "".join(parts[:-1]) + "." + parts[-1]
        else:
            text = "".join(parts)

    try:
        number = float(text)
    except ValueError:
        return None

    return -abs(number) if negative else number


def date_parse(value: Any) -> pd.Timestamp:
    """Parse a date cleanly without triggering Pandas dayfirst warnings."""
    if value is None:
        return pd.NaT

    try:
        if isinstance(value, (pd.Timestamp, datetime)):
            return pd.Timestamp(value)

        text = safe_text(value)
        if not text or re.fullmatch(r"\d{4,}", text):
            return pd.NaT

        # Normalize common date separators.
        # This allows /, \, -, and . to be treated consistently.
        normalized = re.sub(r"[\\/.-]", "/", text.strip())

        # Remove time portion when the date is clearly separated from it.
        date_part = re.split(r"[T\s]", normalized, maxsplit=1)[0]

        # ISO / YYYY-first formats
        if re.match(r"^\d{4}/\d{1,2}/\d{1,2}(?:$|[T\s])", normalized):
            result = pd.to_datetime(text, errors="coerce")

            if pd.notna(result):
                return pd.Timestamp(result)

        # Extract simple numeric date components
        match = re.fullmatch(
            r"(\d{1,4})/(\d{1,2})/(\d{1,4})",
            date_part
        )

        if match:
            first, second, third = map(int, match.groups())

            # YYYY/MM/DD
            if first >= 1000:
                result = pd.to_datetime(
                    f"{first:04d}/{second:02d}/{third:02d}",
                    format="%Y/%m/%d",
                    errors="coerce",
                )

                if pd.notna(result):
                    return pd.Timestamp(result)

            # YY/MM/DD (only when the last component can clearly be a day)
            if first < 100 and third <= 31:
                if second <= 12:
                    result = pd.to_datetime(
                        f"{first:02d}/{second:02d}/{third:02d}",
                        format="%y/%m/%d",
                        errors="coerce",
                    )

                    if pd.notna(result):
                        return pd.Timestamp(result)

            # DD/MM/YYYY or DD/MM/YY
            if first > 12:
                if third >= 1000:
                    result = pd.to_datetime(
                        f"{first}/{second}/{third}",
                        format="%d/%m/%Y",
                        errors="coerce",
                    )
                else:
                    result = pd.to_datetime(
                        f"{first}/{second}/{third}",
                        format="%d/%m/%y",
                        errors="coerce",
                    )

                if pd.notna(result):
                    return pd.Timestamp(result)

            # MM/DD/YYYY or MM/DD/YY
            if second > 12:
                if third >= 1000:
                    result = pd.to_datetime(
                        f"{first}/{second}/{third}",
                        format="%m/%d/%Y",
                        errors="coerce",
                    )
                else:
                    result = pd.to_datetime(
                        f"{first}/{second}/{third}",
                        format="%m/%d/%y",
                        errors="coerce",
                    )

                if pd.notna(result):
                    return pd.Timestamp(result)

            # Ambiguous cases: existing behavior prefers day-first.
            if third >= 1000:
                result = pd.to_datetime(
                    f"{first}/{second}/{third}",
                    format="%d/%m/%Y",
                    errors="coerce",
                )

                if pd.notna(result):
                    return pd.Timestamp(result)

            else:
                result = pd.to_datetime(
                    f"{first}/{second}/{third}",
                    format="%d/%m/%y",
                    errors="coerce",
                )

                if pd.notna(result):
                    return pd.Timestamp(result)

        # Catch remaining formats with warnings suppressed
        with warnings.catch_warnings():
            warnings.simplefilter("ignore", UserWarning)

            result = pd.to_datetime(
                text,
                errors="coerce",
                dayfirst=True
            )

            if pd.notna(result):
                return pd.Timestamp(result)

            result = pd.to_datetime(
                text,
                errors="coerce",
                dayfirst=False
            )

            return pd.Timestamp(result) if pd.notna(result) else pd.NaT

    except Exception:
        return pd.NaT


def date_ratio(series: pd.Series) -> float:
    if len(series) == 0:
        return 0.0
    return float(series.map(date_parse).notna().mean())


def numeric_ratio(series: pd.Series) -> float:
    if len(series) == 0:
        return 0.0
    return float(series.map(numeric_parse).notna().mean())


def unique_ratio(series: pd.Series) -> float:
    if len(series) == 0:
        return 0.0
    values = series.astype(str).replace({"nan": "", "None": ""})
    return float(values.nunique(dropna=True) / max(len(series), 1))


def clamp(value: float, low: float = 0.0, high: float = 100.0) -> float:
    return max(low, min(high, float(value)))


def safe_round(value: Any, digits: int = 4) -> Optional[float]:
    try:
        if value is None or pd.isna(value):
            return None
        return round(float(value), digits)
    except Exception:
        return None


def series_mad(series: pd.Series) -> float:
    """Cross-version compatible Mean Absolute Deviation calculation."""
    if len(series) <= 1:
        return 0.0
    mean_val = series.mean()
    return float((series - mean_val).abs().mean())


COLUMN_HINTS: Dict[str, Sequence[str]] = {
    "date": (
        "date", "time", "timestamp", "posted", "posting", "transaction date",
        "value date", "booking date", "settlement date", "created"
    ),
    "amount": (
        "amount", "value", "total", "transaction", "payment", "settlement",
        "gross", "net", "price", "sum", "proceeds"
    ),
    "debit": (
        "debit", "withdrawal", "outgoing", "outflow", "paid", "charge",
        "expense", "dr"
    ),
    "credit": (
        "credit", "deposit", "incoming", "inflow", "received", "cr"
    ),
    "balance": (
        "balance", "running balance", "closing balance", "available balance"
    ),
    "currency": (
        "currency", "ccy", "curr", "iso currency"
    ),
    "reference": (
        "reference", "ref", "transaction id", "transaction number", "txn",
        "id", "identifier", "invoice", "order", "payment id", "external id"
    ),
    "description": (
        "description", "details", "memo", "narrative", "merchant", "name",
        "payee", "payer", "counterparty", "remarks", "comment", "reason"
    ),
    "fee": (
        "fee", "fees", "commission", "charge", "processing fee", "gateway fee"
    ),
}


def semantic_score(column_name: Any, concept: str) -> float:
    """Score a column name against a generic financial concept."""
    name = normalized_column_name(column_name)
    if not name:
        return 0.0

    score = 0.0
    for hint in COLUMN_HINTS.get(concept, ()):
        h = normalize_text(hint)
        if not h:
            continue

        if name == h:
            score = max(score, 100.0)
        elif h in name:
            score = max(score, 85.0)
        else:
            score = max(score, fuzz.token_set_ratio(name, h))

    return clamp(score)


class FileLoader:
    """Load CSV, Excel and text-based PDF files without fixed schemas."""

    SUPPORTED = {".csv", ".xlsx", ".xls", ".pdf"}

    def load(self, file_path: str) -> List[pd.DataFrame]:
        path = Path(file_path)

        if not path.exists():
            raise FileNotFoundError(f"File does not exist: {file_path}")

        suffix = path.suffix.lower()

        if suffix not in self.SUPPORTED:
            raise ValueError(
                f"Unsupported file type '{suffix}'. "
                f"Supported: {', '.join(sorted(self.SUPPORTED))}"
            )

        if suffix == ".csv":
            return self._load_csv(path)

        if suffix in {".xlsx", ".xls"}:
            return self._load_excel(path)

        return self._load_pdf(path)

    def _load_csv(self, path: Path) -> List[pd.DataFrame]:
        df = pd.read_csv(
            path,
            sep=None,
            engine="python",
            dtype=object,
            keep_default_na=True,
        )

        if df.empty:
            raise ValueError(f"CSV contains no rows: {path}")

        return [self._clean_raw_dataframe(df)]

    def _load_excel(self, path: Path) -> List[pd.DataFrame]:
        workbook = pd.read_excel(path, sheet_name=None, dtype=object)

        frames: List[pd.DataFrame] = []
        for sheet_name, df in workbook.items():
            if df is None or df.empty:
                continue

            cleaned = self._clean_raw_dataframe(df)
            cleaned.attrs["source_sheet"] = str(sheet_name)
            frames.append(cleaned)

        if not frames:
            raise ValueError(f"Excel workbook contains no usable sheets: {path}")

        return frames

    def _load_pdf(self, path: Path) -> List[pd.DataFrame]:
        if pdfplumber is None:
            raise ImportError(
                "pdfplumber is required for PDF input. "
                "Install it with: pip install pdfplumber"
            )

        tables: List[pd.DataFrame] = []

        with pdfplumber.open(path) as pdf:
            for page_number, page in enumerate(pdf.pages, start=1):
                page_tables = page.extract_tables()

                for table_index, table in enumerate(page_tables):
                    if not table:
                        continue

                    rows = []
                    for row in table:
                        if row is None:
                            continue
                        cleaned_row = [safe_text(cell) for cell in row]
                        if any(cleaned_row):
                            rows.append(cleaned_row)

                    if len(rows) < 2:
                        continue

                    df = self._table_to_dataframe(rows)

                    if not df.empty:
                        df.attrs["source_page"] = page_number
                        df.attrs["source_table"] = table_index + 1
                        tables.append(df)

        if tables:
            return tables

        ocr_frames = self._try_ocr_pdf(path)
        if ocr_frames:
            return ocr_frames

        raise ValueError(
            "No tabular data could be extracted from the PDF. "
            "If it is a scanned/image PDF, install OCR dependencies "
            "(pytesseract, pdf2image, pillow) and Tesseract OCR."
        )

    def _table_to_dataframe(self, rows: List[List[str]]) -> pd.DataFrame:
        width = max(len(row) for row in rows)

        normalized_rows = [
            row + [""] * (width - len(row))
            for row in rows
        ]

        best_header_index = 0
        best_score = -1.0

        for index in range(min(5, len(normalized_rows) - 1)):
            row = normalized_rows[index]

            non_empty = sum(bool(normalize_text(x)) for x in row)
            semantic_hits = 0

            for cell in row:
                cell_score = max(
                    semantic_score(cell, "date"),
                    semantic_score(cell, "amount"),
                    semantic_score(cell, "description"),
                    semantic_score(cell, "reference"),
                    semantic_score(cell, "currency"),
                    semantic_score(cell, "debit"),
                    semantic_score(cell, "credit"),
                )
                if cell_score >= 65:
                    semantic_hits += 1

            candidate_score = (
                non_empty * 1.5
                + semantic_hits * 8.0
                - index * 1.0
            )

            if candidate_score > best_score:
                best_score = candidate_score
                best_header_index = index

        header = normalized_rows[best_header_index]

        columns = []
        used: Dict[str, int] = {}

        for i, cell in enumerate(header):
            name = safe_text(cell) or f"column_{i + 1}"
            if name in used:
                used[name] += 1
                name = f"{name}_{used[name]}"
            else:
                used[name] = 1
            columns.append(name)

        data_rows = normalized_rows[best_header_index + 1:]

        if not data_rows:
            return pd.DataFrame()

        return self._clean_raw_dataframe(pd.DataFrame(data_rows, columns=columns))

    @staticmethod
    def _clean_raw_dataframe(df: pd.DataFrame) -> pd.DataFrame:
        result = df.copy()
        result = result.dropna(axis=0, how="all")
        result = result.dropna(axis=1, how="all")

        new_columns = []
        counts: Dict[str, int] = {}

        for i, col in enumerate(result.columns):
            name = safe_text(col) or f"column_{i + 1}"
            if name in counts:
                counts[name] += 1
                name = f"{name}_{counts[name]}"
            else:
                counts[name] = 1
            new_columns.append(name)

        result.columns = new_columns
        result = result.reset_index(drop=True)
        return result

    def _try_ocr_pdf(self, path: Path) -> List[pd.DataFrame]:
        try:
            import pytesseract
            from pdf2image import convert_from_path
        except ImportError:
            return []

        try:
            images = convert_from_path(str(path), dpi=200)
        except Exception:
            return []

        frames: List[pd.DataFrame] = []

        for page_number, image in enumerate(images, start=1):
            try:
                text = pytesseract.image_to_string(image)
            except Exception:
                continue

            lines = [line.strip() for line in text.splitlines() if line.strip()]
            if len(lines) < 2:
                continue

            rows = [re.split(r"\s{2,}|\t+", line) for line in lines]

            if len(rows) >= 2:
                df = self._table_to_dataframe(rows)
                if not df.empty:
                    df.attrs["source_page"] = page_number
                    df.attrs["source_table"] = "ocr"
                    frames.append(df)

        return frames


@dataclass
class Schema:
    date: Optional[str] = None
    amount: Optional[str] = None
    debit: Optional[str] = None
    credit: Optional[str] = None
    balance: Optional[str] = None
    currency: Optional[str] = None
    reference: Optional[str] = None
    description_columns: Tuple[str, ...] = ()
    confidence: Dict[str, float] = None

    def __post_init__(self):
        if self.confidence is None:
            self.confidence = {}


class SchemaDetector:
    """Infer financial fields from unknown tables."""

    def detect(self, df: pd.DataFrame) -> Schema:
        if df is None or df.empty:
            raise ValueError("Cannot detect schema from an empty DataFrame.")

        columns = list(df.columns)

        date_candidates = []
        numeric_candidates = []
        currency_candidates = []
        reference_candidates = []
        description_candidates = []
        debit_candidates = []
        credit_candidates = []
        balance_candidates = []

        for col in columns:
            series = df[col]
            d_ratio = date_ratio(series)
            n_ratio = numeric_ratio(series)
            u_ratio = unique_ratio(series)

            sem_date = semantic_score(col, "date")
            sem_amount = semantic_score(col, "amount")
            sem_currency = semantic_score(col, "currency")
            sem_reference = semantic_score(col, "reference")
            sem_description = semantic_score(col, "description")
            sem_debit = semantic_score(col, "debit")
            sem_credit = semantic_score(col, "credit")
            sem_balance = semantic_score(col, "balance")

            date_candidates.append((col, clamp(d_ratio * 70.0 + sem_date * 0.30)))
            numeric_candidates.append((col, clamp(n_ratio * 70.0 + sem_amount * 0.30)))
            currency_candidates.append((col, sem_currency))
            reference_candidates.append((col, clamp(sem_reference * 0.65 + u_ratio * 35.0)))
            description_candidates.append((col, clamp(sem_description * 0.70 + (1.0 - n_ratio) * 30.0)))
            debit_candidates.append((col, clamp(sem_debit * 0.60 + n_ratio * 40.0)))
            credit_candidates.append((col, clamp(sem_credit * 0.60 + n_ratio * 40.0)))
            balance_candidates.append((col, clamp(sem_balance * 0.70 + n_ratio * 30.0)))

        def best(items):
            return max(items, key=lambda x: x[1]) if items else (None, 0.0)

        date_col, date_conf = best(date_candidates)

        amount_options = []
        for col, score in numeric_candidates:
            penalty = 0.0
            if semantic_score(col, "balance") >= 75:
                penalty += 35.0
            if semantic_score(col, "debit") >= 75:
                penalty += 10.0
            if semantic_score(col, "credit") >= 75:
                penalty += 10.0

            amount_options.append((col, clamp(score - penalty)))

        amount_col, amount_conf = best(amount_options)
        debit_col, debit_conf = best(debit_candidates)
        credit_col, credit_conf = best(credit_candidates)
        balance_col, balance_conf = best(balance_candidates)
        currency_col, currency_conf = best(currency_candidates)
        reference_col, reference_conf = best(reference_candidates)

        if date_conf < 55:
            date_col, date_conf = None, 0.0
        if amount_conf < 55:
            amount_col, amount_conf = None, 0.0
        if currency_conf < 55:
            currency_col, currency_conf = None, 0.0
        if reference_conf < 50:
            reference_col, reference_conf = None, 0.0
        if debit_conf < 55:
            debit_col, debit_conf = None, 0.0
        if credit_conf < 55:
            credit_col, credit_conf = None, 0.0
        if balance_conf < 65:
            balance_col, balance_conf = None, 0.0

        description_cols = []
        excluded = {date_col, amount_col, debit_col, credit_col, balance_col, currency_col, reference_col}

        for col, score in sorted(description_candidates, key=lambda x: x[1], reverse=True):
            if col not in excluded and score >= 45:
                description_cols.append(col)

        description_cols = description_cols[:5]

        return Schema(
            date=date_col,
            amount=amount_col,
            debit=debit_col,
            credit=credit_col,
            balance=balance_col,
            currency=currency_col,
            reference=reference_col,
            description_columns=tuple(description_cols),
            confidence={
                "date": round(date_conf, 2),
                "amount": round(amount_conf, 2),
                "debit": round(debit_conf, 2),
                "credit": round(credit_conf, 2),
                "balance": round(balance_conf, 2),
                "currency": round(currency_conf, 2),
                "reference": round(reference_conf, 2),
                "description": round(
                    max([semantic_score(c, "description") for c in description_cols] or [0.0]),
                    2,
                ),
            },
        )


@dataclass
class Transaction:
    source: str
    source_row: int
    date: pd.Timestamp
    amount: float
    signed_amount: float
    currency: str
    reference: str
    description: str
    raw_values: Dict[str, Any]

    def to_dict(self) -> Dict[str, Any]:
        return {
            "source": self.source,
            "source_row": self.source_row,
            "date": (self.date.strftime("%Y-%m-%d") if pd.notna(self.date) else None),
            "amount": safe_round(self.amount, 2),
            "signed_amount": safe_round(self.signed_amount, 2),
            "currency": self.currency,
            "reference": self.reference,
            "description": self.description,
        }



# ============================================================================
# STRICT ISO 4217 CURRENCY RESOLUTION
# ============================================================================

_CURRENCY_REGISTRY_CACHE = {}

# Explicit symbol mappings only. Text codes and names are validated against
# currencies_iso4217.json; arbitrary three-letter strings are never accepted.
_CURRENCY_SYMBOL_MAP = {
    "€": "EUR",
    "$": "USD",
    "£": "GBP",
    "¥": "JPY",
    "₹": "INR",
    "₩": "KRW",
    "₺": "TRY",
    "₽": "RUB",
    "₫": "VND",
    "฿": "THB",
}


def _load_currency_registry(registry_path=None):
    """
    Load the canonical ISO 4217 registry.

    Searches the explicitly supplied path first, then common taxonomy
    locations relative to the working directory and this Python file.

    Supported optional alias formats:
      - Top-level: {"aliases": {"US DOLLAR": "USD"}}
      - Per currency: {"code": "USD", "aliases": ["US DOLLAR"]}

    Only aliases pointing to registered canonical codes are accepted.
    """
    cache_key = str(registry_path or "__auto__")

    if cache_key in _CURRENCY_REGISTRY_CACHE:
        return _CURRENCY_REGISTRY_CACHE[cache_key]

    candidates = []

    if registry_path:
        candidates.append(Path(registry_path))

    base_dir = Path(__file__).resolve().parent

    candidates.extend([
        Path("taxonomy/currencies_iso4217.json"),
        Path("config/taxonomy/currencies_iso4217.json"),
        base_dir / "taxonomy" / "currencies_iso4217.json",
        base_dir / "config" / "taxonomy" / "currencies_iso4217.json",
        base_dir.parent / "taxonomy" / "currencies_iso4217.json",
        base_dir.parent / "config" / "taxonomy" / "currencies_iso4217.json",
    ])

    seen = set()
    registry = {
        "codes": set(),
        "names": {},
        "aliases": {},
    }

    for candidate in candidates:
        try:
            candidate = candidate.resolve()
        except Exception:
            continue

        if candidate in seen:
            continue
        seen.add(candidate)

        if not candidate.is_file():
            continue

        try:
            with candidate.open("r", encoding="utf-8") as file:
                data = json.load(file)

            currencies = data.get("currencies", [])
            if not isinstance(currencies, list):
                continue

            codes = set()
            names = {}
            aliases = {}

            for item in currencies:
                if not isinstance(item, dict):
                    continue

                code = safe_text(item.get("code")).upper()

                # The registry itself must provide a valid ISO-style code.
                if not re.fullmatch(r"[A-Z]{3}", code):
                    continue

                codes.add(code)

                name = normalize_text(item.get("name"))
                if name:
                    names[name] = code

                item_aliases = item.get("aliases", [])
                if isinstance(item_aliases, str):
                    item_aliases = [item_aliases]

                if isinstance(item_aliases, list):
                    for alias in item_aliases:
                        normalized_alias = normalize_text(alias)
                        if normalized_alias:
                            aliases[normalized_alias] = code

            # Optional top-level alias mapping.
            top_aliases = data.get("aliases", {})
            if isinstance(top_aliases, dict):
                for alias, target in top_aliases.items():
                    normalized_alias = normalize_text(alias)
                    target_code = safe_text(target).upper()

                    if normalized_alias and target_code in codes:
                        aliases[normalized_alias] = target_code

            # Only codes actually present in the JSON are trusted.
            registry = {
                "codes": codes,
                "names": names,
                "aliases": aliases,
            }

            break

        except (OSError, ValueError, TypeError):
            # Try the next supported location.
            continue

    # Cache even an empty result. An absent or invalid registry must not
    # silently cause the engine to fall back to guessing currency codes.
    _CURRENCY_REGISTRY_CACHE[cache_key] = registry
    return registry


def resolve_currency(value, registry_path=None):
    """
    Resolve a value to a registered ISO 4217 code.

    Returns:
        Canonical code such as "EUR" or "USD", or "" when unresolved.

    Examples:
        "EUR"                         -> "EUR" if registered
        "United Arab Emirates dirham" -> "AED" if registered
        "JRN"                         -> "" unless JRN is in the registry
        ""                            -> ""
    """
    raw = safe_text(value)

    if not raw:
        return ""

    registry = _load_currency_registry(registry_path)
    codes = registry["codes"]

    if not codes:
        return ""

    # An explicit standalone currency symbol may be resolved directly,
    # but its target must also exist in the loaded registry.
    stripped = raw.strip()
    if stripped in _CURRENCY_SYMBOL_MAP:
        symbol_code = _CURRENCY_SYMBOL_MAP[stripped]
        return symbol_code if symbol_code in codes else ""

    # Normalize Unicode spaces and letter case for code/name resolution.
    upper = stripped.upper()

    # Accept a canonical code only when it is a standalone token.
    # This does NOT make every three-letter token a currency.
    tokens = re.findall(r"(?<![A-Z])([A-Z]{3})(?![A-Z])", upper)
    registered_tokens = [token for token in tokens if token in codes]

    if len(set(registered_tokens)) == 1:
        return registered_tokens[0]

    # Do not guess when a value contains multiple different currency codes.
    if len(set(registered_tokens)) > 1:
        return ""

    # Match the complete normalized currency name or an explicit alias.
    normalized = normalize_text(stripped)

    if normalized in registry["names"]:
        return registry["names"][normalized]

    if normalized in registry["aliases"]:
        return registry["aliases"][normalized]

    # A symbol embedded in a larger value is accepted only when it has
    # an explicit, registered mapping and no conflicting currency code.
    for symbol, code in _CURRENCY_SYMBOL_MAP.items():
        if symbol in stripped and code in codes:
            return code

    # Unknown values remain unresolved. Never infer from the first
    # three letters or from an unrecognized three-letter token.
    return ""



class TransactionNormalizer:
    """Convert arbitrary detected schemas into a common transaction model."""

    COMMON_CURRENCY_MAP = {
        "€": "EUR", "$": "USD", "£": "GBP", "¥": "JPY",
        "usd": "USD", "eur": "EUR", "gbp": "GBP", "jpy": "JPY",
        "chf": "CHF", "cad": "CAD", "aud": "AUD", "ron": "RON",
        "pln": "PLN", "sek": "SEK", "nok": "NOK", "dkk": "DKK",
    }

    def normalize(self, df: pd.DataFrame, schema: Schema, source_name: str) -> pd.DataFrame:
        if schema.date is None:
            raise ValueError(f"Could not confidently identify a date column in {source_name}.")

        if schema.amount is None and schema.debit is None and schema.credit is None:
            raise ValueError(f"Could not confidently identify a monetary column in {source_name}.")

        records = []

        for index, row in df.iterrows():
            date = date_parse(row.get(schema.date))
            if pd.isna(date):
                continue

            debit = numeric_parse(row.get(schema.debit)) if schema.debit else None
            credit = numeric_parse(row.get(schema.credit)) if schema.credit else None
            generic_amount = numeric_parse(row.get(schema.amount)) if schema.amount else None

            if generic_amount is not None:
                signed_amount = generic_amount
                amount = abs(generic_amount)
            else:
                if credit is not None and debit is not None:
                    signed_amount = credit - debit
                    amount = abs(credit - debit)
                elif credit is not None:
                    signed_amount = abs(credit)
                    amount = abs(credit)
                elif debit is not None:
                    signed_amount = -abs(debit)
                    amount = abs(debit)
                else:
                    continue

            if amount <= 0:
                continue

            currency = self._normalize_currency(row.get(schema.currency)) if schema.currency else ""
            reference = normalize_text(row.get(schema.reference)) if schema.reference else ""

            description_parts = []
            for col in schema.description_columns:
                value = normalize_text(row.get(col))
                if value and value not in description_parts:
                    description_parts.append(value)

            description = " ".join(description_parts).strip()
            raw_values = {str(col): safe_text(row.get(col)) for col in df.columns}

            records.append(
                Transaction(
                    source=source_name,
                    source_row=int(index),
                    date=pd.Timestamp(date),
                    amount=float(abs(amount)),
                    signed_amount=float(signed_amount),
                    currency=currency,
                    reference=reference,
                    description=description,
                    raw_values=raw_values,
                )
            )

        return pd.DataFrame(
            [
                {
                    "_transaction": tx,
                    "source": tx.source,
                    "source_row": tx.source_row,
                    "date": tx.date,
                    "amount": tx.amount,
                    "signed_amount": tx.signed_amount,
                    "currency": tx.currency,
                    "reference": tx.reference,
                    "description": tx.description,
                }
                for tx in records
            ]
        )


    def _normalize_currency(self, value: Any) -> str:
        """Resolve currency strictly against the ISO 4217 taxonomy."""
        return resolve_currency(value)



class DynamicFXEstimator:
    """Estimate FX rates from cross-currency matched data."""

    def __init__(self, config: EngineConfig):
        self.config = config

    def estimate_pair_rates(
        self,
        a: pd.DataFrame,
        b: pd.DataFrame,
    ) -> Dict[Tuple[str, str], Dict[str, Any]]:
        rates: Dict[Tuple[str, str], Dict[str, Any]] = {}

        a_currency = a[a["currency"].astype(bool)]
        b_currency = b[b["currency"].astype(bool)]

        if a_currency.empty or b_currency.empty:
            return rates

        observations: Dict[Tuple[str, str], List[float]] = {}

        for _, row_a in a_currency.iterrows():
            candidates = self._same_day_candidates(row_a, b_currency)

            for _, row_b in candidates.iterrows():
                pair = (row_a["currency"], row_b["currency"])

                if row_a["amount"] <= 0 or row_b["amount"] <= 0:
                    continue

                text_score = max(
                    self._text_similarity(row_a["reference"], row_b["reference"]),
                    self._text_similarity(row_a["description"], row_b["description"]),
                )

                if text_score < 70:
                    continue

                rate = float(row_b["amount"]) / float(row_a["amount"])

                if self.config.min_fx_rate <= rate <= self.config.max_fx_rate:
                    observations.setdefault(pair, []).append(rate)

        for pair, values in observations.items():
            if not values:
                continue

            filtered = self._robust_filter(values)
            if not filtered:
                continue

            median_rate = float(pd.Series(filtered).median())
            spread = series_mad(pd.Series(filtered)) if len(filtered) > 1 else 0.0

            count = len(filtered)
            confidence = clamp(
                55.0 + min(count, 10) * 3.0 + max(0.0, 25.0 - spread * 100.0)
            )

            rates[pair] = {
                "rate": median_rate,
                "observations": count,
                "spread": spread,
                "confidence": confidence,
            }

        return rates

    def _same_day_candidates(self, row_a: pd.Series, b: pd.DataFrame) -> pd.DataFrame:
        if b.empty:
            return b
        delta = (b["date"] - row_a["date"]).abs().dt.days
        return b.loc[delta <= self.config.date_window_days]

    @staticmethod
    def _text_similarity(a: Any, b: Any) -> float:
        aa = safe_text(a)
        bb = safe_text(b)
        if not aa or not bb:
            return 0.0
        return max(float(fuzz.ratio(aa, bb)), float(fuzz.token_set_ratio(aa, bb)))

    @staticmethod
    def _robust_filter(values: Sequence[float]) -> List[float]:
        if len(values) <= 2:
            return list(values)

        series = pd.Series(values, dtype=float)
        median = float(series.median())
        mad = series_mad(series)

        if mad == 0:
            return [float(v) for v in values if abs(float(v) - median) <= max(0.000001, median * 0.01)]

        threshold = 3.5 * mad
        return [float(v) for v in values if abs(float(v) - median) <= threshold]


class MatchScorer:
    """Calculate component scores between two transactions."""

    def __init__(self, config: EngineConfig):
        self.config = config

    def score(
        self,
        a: pd.Series,
        b: pd.Series,
        fx_rate: Optional[float] = None,
    ) -> Dict[str, Any]:
        date_score = self._date_score(a["date"], b["date"])
        amount_score, amount_difference = self._amount_score(
            float(a["amount"]), float(b["amount"]), fx_rate
        )
        reference_score = self._text_score(a.get("reference", ""), b.get("reference", ""))
        description_score = self._text_score(a.get("description", ""), b.get("description", ""))
        direction_score = self._direction_score(
            float(a.get("signed_amount", a["amount"])),
            float(b.get("signed_amount", b["amount"])),
        )

        weights = {
            "amount": 0.42,
            "date": 0.18,
            "reference": 0.18,
            "description": 0.17,
            "direction": 0.05,
        }

        available = {
            "amount": amount_score is not None,
            "date": date_score is not None,
            "reference": reference_score is not None,
            "description": description_score is not None,
            "direction": direction_score is not None,
        }

        total_weight = sum(weight for key, weight in weights.items() if available[key])

        if total_weight == 0:
            overall = 0.0
        else:
            component_values = {
                "amount": amount_score or 0.0,
                "date": date_score or 0.0,
                "reference": reference_score or 0.0,
                "description": description_score or 0.0,
                "direction": direction_score or 0.0,
            }

            weighted = sum(
                component_values[key] * weight
                for key, weight in weights.items()
                if available[key]
            )
            overall = weighted / total_weight

        return {
            "score": round(clamp(overall), 2),
            "amount_score": round(amount_score or 0.0, 2),
            "date_score": round(date_score or 0.0, 2),
            "reference_score": round(reference_score or 0.0, 2),
            "description_score": round(description_score or 0.0, 2),
            "direction_score": round(direction_score or 0.0, 2),
            "amount_difference": (
                round(amount_difference, 2) if amount_difference is not None else None
            ),
            "fx_rate": safe_round(fx_rate, 8),
        }

    def _date_score(self, date_a: pd.Timestamp, date_b: pd.Timestamp) -> Optional[float]:
        if pd.isna(date_a) or pd.isna(date_b):
            return None
        days = abs((date_a - date_b).days)
        if days == 0:
            return 100.0
        if days > self.config.date_window_days:
            return 0.0
        return clamp(100.0 * (1.0 - days / max(self.config.date_window_days, 1)))

    def _amount_score(
        self,
        amount_a: float,
        amount_b: float,
        fx_rate: Optional[float],
    ) -> Tuple[Optional[float], Optional[float]]:
        if amount_a <= 0 or amount_b <= 0:
            return None, None

        comparable_b = amount_b / fx_rate if (fx_rate and fx_rate > 0) else amount_b
        difference = abs(amount_a - comparable_b)
        tolerance = max(self.config.amount_tolerance, amount_a * 0.001)

        if difference <= tolerance:
            return 100.0, difference

        relative_difference = difference / max(abs(amount_a), 1e-9)
        if relative_difference >= 0.25:
            return 0.0, difference

        return clamp(100.0 * (1.0 - relative_difference / 0.25)), difference

    @staticmethod
    def _text_score(a: Any, b: Any) -> Optional[float]:
        aa, bb = safe_text(a), safe_text(b)
        if not aa or not bb:
            return None
        return max(float(fuzz.ratio(aa, bb)), float(fuzz.token_set_ratio(aa, bb)))

    @staticmethod
    def _direction_score(a: float, b: float) -> Optional[float]:
        if a == 0 or b == 0:
            return None
        return 100.0 if (a > 0) == (b > 0) else 0.0


class SplitPaymentDetector:
    """Detect one-to-many or many-to-one payment relationships."""

    def __init__(self, config: EngineConfig):
        self.config = config

    def find_split(self, target: pd.Series, candidates: pd.DataFrame) -> Optional[Dict[str, Any]]:
        if candidates.empty:
            return None

        target_amount = float(target["amount"])
        if target_amount <= 0:
            return None

        target_date = target["date"]
        work = candidates.copy()

        date_delta = (work["date"] - target_date).abs().dt.days
        work = work.loc[date_delta <= self.config.split_window_days].copy()

        if work.empty:
            return None

        work = work.loc[work["amount"] <= target_amount + self.config.amount_tolerance].copy()
        if work.empty:
            return None

        def relation_score(row):
            ref = max(
                self._text_similarity(target.get("reference", ""), row.get("reference", "")),
                self._text_similarity(target.get("description", ""), row.get("description", "")),
            )
            date_score = 100.0 - min(abs((row["date"] - target_date).days) * 20.0, 100.0)
            return 0.7 * ref + 0.3 * date_score

        work["_split_relation"] = work.apply(relation_score, axis=1)
        work = work.sort_values(["_split_relation", "amount"], ascending=[False, False]).head(30)

        rows = list(work.iterrows())
        best = None

        def search(start, chosen, current_sum):
            nonlocal best
            difference = abs(target_amount - current_sum)

            if chosen and difference <= self.config.amount_tolerance:
                score = self._split_score(target, [rows[i][1] for i in chosen])
                candidate = {
                    "indices": [rows[i][0] for i in chosen],
                    "difference": difference,
                    "score": score,
                }
                if best is None or candidate["score"] > best["score"]:
                    best = candidate
                return

            if len(chosen) >= self.config.max_split_items or current_sum >= target_amount:
                return

            for i in range(start, len(rows)):
                row = rows[i][1]
                new_sum = current_sum + float(row["amount"])
                if new_sum > target_amount + self.config.amount_tolerance:
                    continue
                search(i + 1, chosen + [i], new_sum)

        search(0, [], 0.0)

        if best is None or best["score"] < self.config.match_threshold:
            return None

        return best

    def _split_score(self, target: pd.Series, rows: Sequence[pd.Series]) -> float:
        if not rows:
            return 0.0

        date_scores, text_scores = [], []
        for row in rows:
            days = abs((row["date"] - target["date"]).days)
            date_scores.append(clamp(100.0 * (1.0 - days / max(self.config.split_window_days, 1))))
            text_scores.append(
                max(
                    self._text_similarity(target.get("reference", ""), row.get("reference", "")),
                    self._text_similarity(target.get("description", ""), row.get("description", "")),
                )
            )

        return clamp(
            70.0
            + 0.15 * (sum(date_scores) / len(date_scores))
            + 0.15 * (sum(text_scores) / len(text_scores))
        )

    @staticmethod
    def _text_similarity(a: Any, b: Any) -> float:
        aa, bb = safe_text(a), safe_text(b)
        if not aa or not bb:
            return 0.0
        return max(float(fuzz.ratio(aa, bb)), float(fuzz.token_set_ratio(aa, bb)))


class GatewayFeeInferer:
    """Infer fee differences between gross and settlement transactions."""

    def __init__(self, config: EngineConfig):
        self.config = config

    def learn_fee_profiles(self, a: pd.DataFrame, b: pd.DataFrame) -> List[Dict[str, Any]]:
        observations = []

        for _, row_a in a.iterrows():
            candidates = self._candidate_rows(row_a, b)

            for _, row_b in candidates.iterrows():
                gross = max(float(row_a["amount"]), float(row_b["amount"]))
                net = min(float(row_a["amount"]), float(row_b["amount"]))

                if gross <= self.config.min_amount_for_fee_inference:
                    continue

                fee = gross - net
                fee_percent = fee / gross

                if (
                    fee >= self.config.fee_tolerance
                    and fee_percent <= self.config.max_reasonable_fee_percent
                ):
                    text_score = max(
                        self._text_similarity(row_a.get("reference", ""), row_b.get("reference", "")),
                        self._text_similarity(row_a.get("description", ""), row_b.get("description", "")),
                    )

                    if text_score >= 60:
                        observations.append(
                            {
                                "fee": fee,
                                "fee_percent": fee_percent,
                                "text_score": text_score,
                            }
                        )

        if not observations:
            return []

        # Cluster fee percentages into 0.5% tolerance buckets
        profiles, used = [], set()
        for i, obs in enumerate(observations):
            if i in used:
                continue

            cluster = [obs]
            used.add(i)

            for j, other in enumerate(observations):
                if j in used:
                    continue
                if abs(other["fee_percent"] - obs["fee_percent"]) <= 0.005:
                    cluster.append(other)
                    used.add(j)

            # Ignore isolated noise observations with low counts (< 2)
            if len(cluster) < 2 and len(observations) > 10:
                continue

            percentages = [x["fee_percent"] for x in cluster]
            fees = [x["fee"] for x in cluster]

            profiles.append(
                {
                    "fee_percent": float(pd.Series(percentages).median()),
                    "median_fee": float(pd.Series(fees).median()),
                    "observations": len(cluster),
                    "confidence": clamp(60.0 + min(len(cluster), 10) * 4.0),
                }
            )

        profiles.sort(key=lambda x: (x["observations"], x["confidence"]), reverse=True)
        return profiles

    def infer(
        self,
        target: pd.Series,
        candidate: pd.Series,
        profiles: Sequence[Dict[str, Any]],
    ) -> Optional[Dict[str, Any]]:
        a_val, b_val = float(target["amount"]), float(candidate["amount"])
        gross, net = max(a_val, b_val), min(a_val, b_val)

        if gross <= self.config.min_amount_for_fee_inference:
            return None

        fee = gross - net
        fee_percent = fee / gross

        if fee < self.config.fee_tolerance or fee_percent > self.config.max_reasonable_fee_percent:
            return None

        text_score = max(
            self._text_similarity(target.get("reference", ""), candidate.get("reference", "")),
            self._text_similarity(target.get("description", ""), candidate.get("description", "")),
        )

        days = abs((target["date"] - candidate["date"]).days)
        if days > self.config.date_window_days:
            return None

        profile_score = 0.0
        matched_profile = None

        for profile in profiles:
            diff = abs(profile["fee_percent"] - fee_percent)
            score = 100.0 if diff <= 0.005 else (80.0 if diff <= 0.015 else (60.0 if diff <= 0.03 else 0.0))
            if score > profile_score:
                profile_score = score
                matched_profile = profile

        date_score = clamp(100.0 * (1.0 - days / max(self.config.date_window_days, 1)))
        confidence = clamp(50.0 + 0.25 * date_score + 0.20 * text_score + 0.25 * profile_score)

        if confidence < self.config.match_threshold:
            return None

        return {
            "gross_amount": round(gross, 2),
            "settled_amount": round(net, 2),
            "fee_amount": round(fee, 2),
            "fee_percent": round(fee_percent * 100.0, 4),
            "confidence": round(confidence, 2),
            "profile": matched_profile,
        }

    def _candidate_rows(self, target: pd.Series, b: pd.DataFrame) -> pd.DataFrame:
        if b.empty:
            return b
        delta = (b["date"] - target["date"]).abs().dt.days
        return b.loc[delta <= self.config.date_window_days]

    @staticmethod
    def _text_similarity(a: Any, b: Any) -> float:
        aa, bb = safe_text(a), safe_text(b)
        if not aa or not bb:
            return 0.0
        return max(float(fuzz.ratio(aa, bb)), float(fuzz.token_set_ratio(aa, bb)))


# ======================================================================
# POST-MATCH EXCEPTION AUDIT
# ----------------------------------------------------------------------
# The matching passes answer "are these two rows the same transaction?".
# This audit answers "is the pair actually clean?".  It runs a full
# two-way join on the trace ID and emits categorized exceptions.
# It never mutates the matching results or the input files.
# ======================================================================

_AUDIT_ENTITY_COL = re.compile(
    r"counterparty|entity|party|payee|payer|vendor|customer|merchant|company|name",
    re.I,
)
_AUDIT_ENTITY_EXCLUDE = re.compile(
    r"ref|descr|memo|narrat|remark|comment|reason|id$|number|file|source",
    re.I,
)
_AUDIT_DIRECTION_COL = re.compile(
    r"direction|dr.?cr|debit.?credit|flow|movement|type|side|indicator",
    re.I,
)


def extract_trace_key(
    reference: str,
    description: str,
    fallback: str,
    pattern: Optional[str] = None,
) -> str:
    """
    Identity key for a transaction.

    Default: the LAST group of digits in the reference, as an integer, so
    'jrn 2025 00450' and 'st 000450' both become '450'.
    Falls back to 'bnk25 00450'-style tokens in the description, then to a
    unique placeholder so the row is reported as an orphan instead of being
    silently dropped.
    """
    ref = reference or ""

    if pattern:
        try:
            matches = list(re.finditer(pattern, ref, flags=re.I))
            if matches:
                last = matches[-1]
                token = last.group(1) if last.groups() else last.group(0)
                digits = re.findall(r"\d+", token)
                if digits:
                    return str(int(digits[-1]))
        except re.error:
            pass

    nums = re.findall(r"\d+", ref)
    if nums:
        return str(int(nums[-1]))

    match = re.search(r"bnk\d*\s+(\d+)", description or "")
    if match:
        return str(int(match.group(1)))

    return fallback


def _audit_direction(tx: Transaction, column: Optional[str] = None) -> str:
    """Return 'DEBIT' or 'CREDIT' for a transaction."""
    if column and column in tx.raw_values:
        text = safe_text(tx.raw_values[column]).upper()
        if text in ("DEBIT", "DR", "D"):
            return "DEBIT"
        if text in ("CREDIT", "CR", "C"):
            return "CREDIT"

    # Prefer columns whose NAME looks like a direction flag.
    for col, value in tx.raw_values.items():
        if _AUDIT_DIRECTION_COL.search(str(col)):
            text = safe_text(value).upper()
            if text in ("DEBIT", "DR"):
                return "DEBIT"
            if text in ("CREDIT", "CR"):
                return "CREDIT"

    # Any cell that is exactly DEBIT/CREDIT.
    for value in tx.raw_values.values():
        text = safe_text(value).upper()
        if text in ("DEBIT", "DR"):
            return "DEBIT"
        if text in ("CREDIT", "CR"):
            return "CREDIT"

    return "CREDIT" if tx.signed_amount > 0 else "DEBIT"


def _audit_entity(tx: Transaction, column: Optional[str] = None) -> str:
    """Return the normalized counterparty name, or '' when not found."""
    if column and column in tx.raw_values:
        return normalize_text(tx.raw_values[column])

    for col, value in tx.raw_values.items():
        name = str(col)
        if _AUDIT_ENTITY_EXCLUDE.search(name):
            continue
        if _AUDIT_ENTITY_COL.search(name) and safe_text(value):
            return normalize_text(value)

    return ""


def audit_exceptions(
    a: pd.DataFrame,
    b: pd.DataFrame,
    date_window_days: int,
    entity_column: Optional[str] = None,
    direction_column: Optional[str] = None,
    trace_pattern: Optional[str] = None,
) -> Dict[str, Any]:
    """
    Two-way identity audit between normalized Source A (ledger) and
    Source B (statement).

    Exception types:
        UNMATCHED_LEDGER_ORPHAN     in A only
        UNMATCHED_STATEMENT_ORPHAN  in B only
        AMOUNT_MISMATCH             same trace ID, amounts differ (to the cent)
        ENTITY_MISMATCH             same trace ID, counterparty differs
        DIRECTION_MISMATCH          same trace ID, DEBIT/CREDIT differs
    Date differences are NOT exceptions; they are logged separately.
    """

    def build_index(df: pd.DataFrame) -> Dict[str, List[Transaction]]:
        index: Dict[str, List[Transaction]] = {}
        if df is None or df.empty or "_transaction" not in df.columns:
            return index
        for _, row in df.iterrows():
            tx: Transaction = row["_transaction"]
            key = extract_trace_key(
                tx.reference,
                tx.description,
                fallback=f"__nokey_{tx.source}_{tx.source_row}",
                pattern=trace_pattern,
            )
            index.setdefault(key, []).append(tx)
        return index

    index_a = build_index(a)
    index_b = build_index(b)

    items: List[Dict[str, Any]] = []
    timing_shifts: List[Dict[str, Any]] = []

    def add(kind, key, ta=None, tb=None, **extra):
        items.append(
            {
                "type": kind,
                "trace_key": key,
                "source_a_row": int(ta.source_row) if ta is not None else None,
                "source_b_row": int(tb.source_row) if tb is not None else None,
                "date_a": ta.date.strftime("%Y-%m-%d") if ta is not None else None,
                "date_b": tb.date.strftime("%Y-%m-%d") if tb is not None else None,
                "amount_a": safe_round(ta.amount, 2) if ta is not None else None,
                "amount_b": safe_round(tb.amount, 2) if tb is not None else None,
                "reference_a": ta.reference if ta is not None else None,
                "reference_b": tb.reference if tb is not None else None,
                "ledger_row": ta.to_dict() if ta is not None else None,
                "statement_row": tb.to_dict() if tb is not None else None,
                **extra,
            }
        )

    def sort_key(key: str):
        return (0, int(key)) if key.isdigit() else (1, key)

    for key in sorted(set(index_a) | set(index_b), key=sort_key):
        list_a = sorted(index_a.get(key, []), key=lambda t: (t.date, t.source_row))
        list_b = sorted(index_b.get(key, []), key=lambda t: (t.date, t.source_row))

        paired = min(len(list_a), len(list_b))

        # Orphans (two-way)
        for ta in list_a[paired:]:
            add("UNMATCHED_LEDGER_ORPHAN", key, ta=ta)
        for tb in list_b[paired:]:
            add("UNMATCHED_STATEMENT_ORPHAN", key, tb=tb)

        # Pair comparators
        for ta, tb in zip(list_a[:paired], list_b[:paired]):
            if round(ta.amount * 100) != round(tb.amount * 100):
                add(
                    "AMOUNT_MISMATCH", key, ta, tb,
                    difference=round(abs(ta.amount - tb.amount), 2),
                )

            entity_a = _audit_entity(ta, entity_column)
            entity_b = _audit_entity(tb, entity_column)
            if entity_a and entity_b and entity_a != entity_b:
                add("ENTITY_MISMATCH", key, ta, tb, entity_a=entity_a, entity_b=entity_b)

            direction_a = _audit_direction(ta, direction_column)
            direction_b = _audit_direction(tb, direction_column)
            if direction_a != direction_b:
                add(
                    "DIRECTION_MISMATCH", key, ta, tb,
                    direction_a=direction_a, direction_b=direction_b,
                )

            days = abs((ta.date - tb.date).days)
            if days > 0:
                timing_shifts.append(
                    {
                        "trace_key": key,
                        "date_a": ta.date.strftime("%Y-%m-%d"),
                        "date_b": tb.date.strftime("%Y-%m-%d"),
                        "days": int(days),
                        "within_window": bool(days <= date_window_days),
                    }
                )

    counts: Dict[str, int] = {}
    for item in items:
        counts[item["type"]] = counts.get(item["type"], 0) + 1

    return {
        "items": items,
        "counts": counts,
        "total": len(items),
        "timing_shifts_ignored": timing_shifts,
        "timing_shift_count": len(timing_shifts),
    }


def _exception_detail(item: Dict[str, Any]) -> str:
    """Short human-readable explanation shown in the report."""
    kind = item["type"]
    if kind == "UNMATCHED_LEDGER_ORPHAN":
        return "Present in ledger (Source A) but missing from the statement (Source B)."
    if kind == "UNMATCHED_STATEMENT_ORPHAN":
        return "Present in the statement (Source B) but missing from the ledger (Source A)."
    if kind == "AMOUNT_MISMATCH":
        return (
            f"Amount differs: A={item.get('amount_a')} vs B={item.get('amount_b')} "
            f"(difference {item.get('difference')})."
        )
    if kind == "ENTITY_MISMATCH":
        return f"Counterparty differs: A='{item.get('entity_a')}' vs B='{item.get('entity_b')}'."
    if kind == "DIRECTION_MISMATCH":
        return f"Direction differs: A={item.get('direction_a')} vs B={item.get('direction_b')}."
    return "Record could not be reconciled."


def _tx_row_to_dict(tx_dict: Dict[str, Any]) -> Dict[str, Any]:
    """Keep the exact key order the report already renders."""
    keys = ["source", "source_row", "date", "amount", "signed_amount",
            "currency", "reference", "description"]
    return {k: tx_dict.get(k) for k in keys}


def build_exception_rows(
    items: List[Dict[str, Any]],
) -> Tuple[List[Dict[str, Any]], List[Dict[str, Any]]]:
    """
    Convert audit exceptions into separate Source A and Source B report rows.

    Paired discrepancies appear in BOTH source reports:
      - Source A gets the ledger transaction and Source B counterpart details.
      - Source B gets the statement transaction and Source A counterpart details.

    Orphan transactions appear only in the report for their own source.
    exception_detail, difference, trace_key, and counterpart fields are retained.
    """
    rows_a: List[Dict[str, Any]] = []
    rows_b: List[Dict[str, Any]] = []

    for item in items:
        ledger = item.get("ledger_row")
        statement = item.get("statement_row")

        exception_type = item.get("type")
        detail = _exception_detail(item)
        trace_key = item.get("trace_key")

        common_fields = {
            "exception_type": exception_type,
            "exception_detail": detail,
            "trace_key": trace_key,
            "difference": item.get("difference"),
        }

        # Source A: ledger transaction.
        if ledger is not None:
            row_a = _tx_row_to_dict(ledger)
            row_a.update(common_fields)

            if statement is not None:
                row_a.update({
                    "counterpart_source_row": statement.get("source_row"),
                    "counterpart_date": statement.get("date"),
                    "counterpart_amount": statement.get("amount"),
                    "counterpart_reference": statement.get("reference"),
                })

            rows_a.append(row_a)

        # Source B: statement transaction.
        # IMPORTANT: include paired discrepancies here too, not just orphans.
        if statement is not None:
            row_b = _tx_row_to_dict(statement)
            row_b.update(common_fields)

            if ledger is not None:
                row_b.update({
                    "counterpart_source_row": ledger.get("source_row"),
                    "counterpart_date": ledger.get("date"),
                    "counterpart_amount": ledger.get("amount"),
                    "counterpart_reference": ledger.get("reference"),
                })

            rows_b.append(row_b)

    return rows_a, rows_b

class SmartReconEngine:
    """Main orchestration class for reconciliation."""

    def __init__(self, config: Optional[EngineConfig] = None):
        self.config = config or EngineConfig()
        self.loader = FileLoader()
        self.schema_detector = SchemaDetector()
        self.normalizer = TransactionNormalizer()
        self.fx_estimator = DynamicFXEstimator(self.config)
        self.scorer = MatchScorer(self.config)
        self.split_detector = SplitPaymentDetector(self.config)
        self.fee_inferer = GatewayFeeInferer(self.config)

    def load_and_normalize(
        self,
        file_input: Union[str, Path, pd.DataFrame],
        default_source_name: str = "Source",
    ) -> Tuple[pd.DataFrame, List[Dict[str, Any]]]:
        """
        Load and normalize transaction data from either a file path or an
        in-memory pandas DataFrame.
        """
        if isinstance(file_input, pd.DataFrame):
            frames = [file_input]
            source_name = default_source_name
        else:
            frames = self.loader.load(str(file_input))
            source_name = Path(file_input).name

        all_transactions = []
        schemas = []

        for table_index, df in enumerate(frames):
            schema = self.schema_detector.detect(df)

            sheet = df.attrs.get("source_sheet")
            page = df.attrs.get("source_page")
            table = df.attrs.get("source_table")

            location_parts = [source_name]
            if sheet:
                location_parts.append(f"sheet={sheet}")
            if page:
                location_parts.append(f"page={page}")
            if table:
                location_parts.append(f"table={table}")
            elif len(frames) > 1:
                location_parts.append(f"table={table_index + 1}")

            source_label = " | ".join(location_parts)
            normalized = self.normalizer.normalize(df, schema, source_label)

            if not normalized.empty:
                # Apply date range filtering if specified by Laravel/UI
                if self.config.start_date:
                    start_ts = pd.to_datetime(self.config.start_date, errors="coerce")
                    if pd.notna(start_ts):
                        normalized = normalized[normalized["date"] >= start_ts]

                if self.config.end_date:
                    end_ts = pd.to_datetime(self.config.end_date, errors="coerce")
                    if pd.notna(end_ts):
                        normalized = normalized[normalized["date"] <= end_ts]

                if not normalized.empty:
                    all_transactions.append(normalized)

            schemas.append({"source": source_label, "schema": asdict(schema)})

        if not all_transactions:
            raise ValueError(f"No valid financial transactions could be detected in {source_name}.")

        return pd.concat(all_transactions, ignore_index=True), schemas

    def reconcile(
        self,
        df_a: Union[str, Path, pd.DataFrame],
        df_b: Union[str, Path, pd.DataFrame],
        source_a_name: str = "Source_A",
        source_b_name: str = "Source_B",
    ) -> Dict[str, Any]:

        # Normalize incoming preprocessed DataFrames or file paths
        a, schemas_a = self.load_and_normalize(df_a, default_source_name=source_a_name)
        b, schemas_b = self.load_and_normalize(df_b, default_source_name=source_b_name)

        # Pass conditional estimation based on pipeline toggles
        fx_rates = self.fx_estimator.estimate_pair_rates(a, b) if self.config.enable_fx else {}
        fee_profiles = self.fee_inferer.learn_fee_profiles(a, b) if self.config.enable_gateway else []

        unmatched_a = set(a.index)
        unmatched_b = set(b.index)
        results: List[Dict[str, Any]] = []

        # Pass 1: Exact matches (always enabled as baseline)
        self._exact_match_pass(a, b, unmatched_a, unmatched_b, results, fx_rates)

        # Pass 2: Fuzzy matches (if enabled)
        if self.config.enable_fuzzy:
            self._fuzzy_match_pass(a, b, unmatched_a, unmatched_b, results, fx_rates)

        # Pass 3: Gateway fee matches (if enabled)
        if self.config.enable_gateway:
            self._fee_match_pass(a, b, unmatched_a, unmatched_b, results, fee_profiles)

        # Pass 4: Split payment matches (if enabled)
        if self.config.enable_split:
            self._split_match_pass(a, b, unmatched_a, unmatched_b, results)

        engine_unmatched_a_df = a.loc[sorted(unmatched_a)].copy()
        engine_unmatched_b_df = b.loc[sorted(unmatched_b)].copy()
        matches_df = pd.DataFrame(results)

        # --------------------------------------------------------------
        # Post-match exception audit
        # --------------------------------------------------------------
        audit_failed = False
        try:
            exceptions = audit_exceptions(
                a,
                b,
                self.config.date_window_days,
                entity_column=self.config.audit_entity_column,
                direction_column=self.config.audit_direction_column,
                trace_pattern=self.config.audit_trace_pattern,
            )
        except Exception as exc:
            audit_failed = True
            exceptions = {
                "items": [],
                "counts": {},
                "total": 0,
                "timing_shifts_ignored": [],
                "timing_shift_count": 0,
                "error": f"{type(exc).__name__}: {exc}",
            }

        # Decide what the report shows as "unmatched / exceptions".
        use_exceptions = self.config.report_exceptions_as_unmatched and not audit_failed

        if use_exceptions:
            rows_a, rows_b = build_exception_rows(exceptions["items"])

            # Safety net: engine-unmatched rows that the audit did not flag
            # (e.g. identical records the matcher failed to pair) must never
            # disappear from the report.
            covered_a = {r["source_row"] for r in rows_a}
            covered_a |= {i["source_a_row"] for i in exceptions["items"] if i["source_a_row"] is not None}
            covered_b = {r["source_row"] for r in rows_b}
            covered_b |= {i["source_b_row"] for i in exceptions["items"] if i["source_b_row"] is not None}
            unresolved = 0

            for _, r in engine_unmatched_a_df.iterrows():
                if int(r["source_row"]) not in covered_a:
                    row = _tx_row_to_dict(r["_transaction"].to_dict())
                    row.update(
                        {
                            "exception_type": "UNRESOLVED_ENGINE_UNMATCHED",
                            "exception_detail": "Engine could not match this ledger record and no specific discrepancy was identified.",
                            "trace_key": None,
                        }
                    )
                    rows_a.append(row)
                    unresolved += 1

            for _, r in engine_unmatched_b_df.iterrows():
                if int(r["source_row"]) not in covered_b:
                    row = _tx_row_to_dict(r["_transaction"].to_dict())
                    row.update(
                        {
                            "exception_type": "UNRESOLVED_ENGINE_UNMATCHED",
                            "exception_detail": "Engine could not match this statement record and no specific discrepancy was identified.",
                            "trace_key": None,
                        }
                    )
                    rows_b.append(row)
                    unresolved += 1

            if unresolved:
                exceptions["counts"]["UNRESOLVED_ENGINE_UNMATCHED"] = unresolved
                exceptions["total"] += unresolved

            report_a_df = pd.DataFrame(rows_a)
            report_b_df = pd.DataFrame(rows_b)
        else:
            report_a_df = engine_unmatched_a_df
            report_b_df = engine_unmatched_b_df

        summary = self._build_summary(
            a, b, matches_df, report_a_df, report_b_df,
            schemas_a, schemas_b, fx_rates, fee_profiles
        )

        # Additive summary fields (existing keys keep their names).
        summary["exception_total"] = int(exceptions.get("total", 0))
        summary["exception_counts"] = dict(exceptions.get("counts", {}))
        summary["timing_shift_count"] = int(exceptions.get("timing_shift_count", 0))
        summary["engine_unmatched_source_a"] = int(len(engine_unmatched_a_df))
        summary["engine_unmatched_source_b"] = int(len(engine_unmatched_b_df))
        summary["engine_match_rate_source_a_percent"] = summary["match_rate_source_a_percent"]

        if use_exceptions:
            involved_a = {
                i["source_a_row"] for i in exceptions["items"] if i["source_a_row"] is not None
            }
            involved_a |= {int(r["source_row"]) for r in report_a_df.to_dict("records")} if not report_a_df.empty else set()
            clean_rate = 100.0 * (len(a) - len(involved_a)) / max(len(a), 1)
            # The headline match rate now excludes records that carry a discrepancy.
            summary["match_rate_source_a_percent"] = round(clean_rate, 2)

        if audit_failed:
            summary["exception_audit_error"] = exceptions.get("error")

        return {
            "matches": matches_df,
            "unmatched_a": report_a_df,
            "unmatched_b": report_b_df,
            "unmatched_a_rows": _serialize_df_rows(report_a_df),
            "unmatched_b_rows": _serialize_df_rows(report_b_df),
            "summary": summary,
            "schemas_a": schemas_a,
            "schemas_b": schemas_b,
            "fx_rates": fx_rates,
            "fee_profiles": fee_profiles,
            "exceptions": exceptions,
        }

    def _exact_match_pass(
        self,
        a: pd.DataFrame,
        b: pd.DataFrame,
        unmatched_a: set,
        unmatched_b: set,
        results: List[Dict[str, Any]],
        fx_rates: Dict[Tuple[str, str], Dict[str, Any]],
    ):
        for idx_a in list(unmatched_a):
            row_a = a.loc[idx_a]
            candidates = self._candidate_pool(row_a, b.loc[list(unmatched_b)])
            best = None

            for idx_b, row_b in candidates.iterrows():
                if row_a["currency"] and row_b["currency"]:
                    fx_rate = 1.0 if row_a["currency"] == row_b["currency"] else self._get_fx_rate(row_a["currency"], row_b["currency"], fx_rates)
                else:
                    fx_rate = None

                ref_match = (
                    bool(row_a["reference"])
                    and bool(row_b["reference"])
                    and row_a["reference"] == row_b["reference"]
                )
                amount_close = self._amount_close(row_a["amount"], row_b["amount"], fx_rate)
                days = abs((row_a["date"] - row_b["date"]).days)

                if ref_match and amount_close and days <= self.config.date_window_days:
                    score = self.scorer.score(row_a, row_b, fx_rate)
                    if best is None or score["score"] > best["score"]["score"]:
                        best = {"idx_b": idx_b, "row_b": row_b, "score": score}

            if best:
                idx_b = best["idx_b"]
                self._record_match(
                    row_a, best["row_b"], best["score"], "EXACT", results,
                    fx_rate=best["score"].get("fx_rate")
                )
                unmatched_a.remove(idx_a)
                unmatched_b.remove(idx_b)

    def _fuzzy_match_pass(
        self,
        a: pd.DataFrame,
        b: pd.DataFrame,
        unmatched_a: set,
        unmatched_b: set,
        results: List[Dict[str, Any]],
        fx_rates: Dict[Tuple[str, str], Dict[str, Any]],
    ):
        for idx_a in list(unmatched_a):
            row_a = a.loc[idx_a]
            candidates = self._candidate_pool(row_a, b.loc[list(unmatched_b)])
            best = None

            for idx_b, row_b in candidates.iterrows():
                fx_rate = self._get_compatible_fx(row_a, row_b, fx_rates)
                score = self.scorer.score(row_a, row_b, fx_rate)

                if score["score"] < self.config.candidate_threshold:
                    continue

                context_score = max(
                    score["reference_score"], score["description_score"], score["date_score"]
                )

                if score["amount_score"] >= 70 and context_score >= self.config.fuzzy_threshold:
                    if best is None or score["score"] > best["score"]["score"]:
                        best = {"idx_b": idx_b, "row_b": row_b, "score": score}

            if best and best["score"]["score"] >= self.config.match_threshold:
                idx_b = best["idx_b"]
                self._record_match(
                    row_a, best["row_b"], best["score"], "FUZZY", results,
                    fx_rate=best["score"].get("fx_rate")
                )
                unmatched_a.remove(idx_a)
                unmatched_b.remove(idx_b)

    def _fee_match_pass(
        self,
        a: pd.DataFrame,
        b: pd.DataFrame,
        unmatched_a: set,
        unmatched_b: set,
        results: List[Dict[str, Any]],
        fee_profiles: Sequence[Dict[str, Any]],
    ):
        for idx_a in list(unmatched_a):
            row_a = a.loc[idx_a]
            candidates = self._candidate_pool(row_a, b.loc[list(unmatched_b)])
            best = None

            for idx_b, row_b in candidates.iterrows():
                inferred = self.fee_inferer.infer(row_a, row_b, fee_profiles)
                if inferred and (best is None or inferred["confidence"] > best["inferred"]["confidence"]):
                    best = {"idx_b": idx_b, "row_b": row_b, "inferred": inferred}

            if best:
                idx_b = best["idx_b"]
                row_b = best["row_b"]
                inferred = best["inferred"]

                days = abs((row_a["date"] - row_b["date"]).days)
                date_score = clamp(100.0 * (1.0 - days / max(self.config.date_window_days, 1)))
                text_score = max(
                    self.fee_inferer._text_similarity(row_a.get("reference", ""), row_b.get("reference", "")),
                    self.fee_inferer._text_similarity(row_a.get("description", ""), row_b.get("description", "")),
                )

                result = self._base_result(row_a, row_b, "GATEWAY_FEE", inferred["confidence"])
                result.update({
                    "gross_amount": inferred["gross_amount"],
                    "settled_amount": inferred["settled_amount"],
                    "fee_amount": inferred["fee_amount"],
                    "fee_percent": inferred["fee_percent"],
                    "date_score": round(date_score, 2),
                    "context_score": round(text_score, 2),
                    "reason": "Amounts differ by a plausible gateway fee; date and context support the match.",
                })
                results.append(result)
                unmatched_a.remove(idx_a)
                unmatched_b.remove(idx_b)

    def _split_match_pass(
        self,
        a: pd.DataFrame,
        b: pd.DataFrame,
        unmatched_a: set,
        unmatched_b: set,
        results: List[Dict[str, Any]],
    ):
        for idx_a in list(unmatched_a):
            target = a.loc[idx_a]
            split = self.split_detector.find_split(target, b.loc[list(unmatched_b)])
            if split is None:
                continue

            selected_indices = split["indices"]
            selected_rows = b.loc[selected_indices]

            result = self._base_result(target, selected_rows.iloc[0], "SPLIT_PAYMENT", split["score"])
            result.update({
                "matched_source_rows": [int(x) for x in selected_rows["source_row"].tolist()],
                "matched_amounts": [round(float(x), 2) for x in selected_rows["amount"].tolist()],
                "cluster_total": round(float(selected_rows["amount"].sum()), 2),
                "difference": round(float(split["difference"]), 2),
                "reason": "Target transaction matched to a cluster of multiple transactions.",
            })
            results.append(result)

            unmatched_a.remove(idx_a)
            for idx_b in selected_indices:
                if idx_b in unmatched_b:
                    unmatched_b.remove(idx_b)

    def _candidate_pool(self, row_a: pd.Series, b: pd.DataFrame) -> pd.DataFrame:
        if b.empty:
            return b

        work = b.copy()
        date_delta = (work["date"] - row_a["date"]).abs().dt.days
        work = work.loc[
            date_delta <= max(self.config.date_window_days, self.config.split_window_days)
        ].copy()

        if work.empty:
            return work

        if row_a.get("currency"):
            same_currency = work["currency"] == row_a["currency"]
            unknown_currency = work["currency"] == ""
            cross_currency = work["currency"] != row_a["currency"]
            work = work.loc[same_currency | unknown_currency | cross_currency].copy()

        def quick_score(row):
            ref = max(self._text_similarity(row_a.get("reference", ""), row.get("reference", "")), 0.0)
            desc = max(self._text_similarity(row_a.get("description", ""), row.get("description", "")), 0.0)
            amount_diff = abs(float(row_a["amount"]) - float(row["amount"]))
            amount_scale = max(float(row_a["amount"]), 1.0)
            amount_score = clamp(100.0 * (1.0 - min(amount_diff / amount_scale, 1.0)))
            return 0.45 * amount_score + 0.30 * ref + 0.25 * desc

        work["_quick_score"] = work.apply(quick_score, axis=1)
        return work.sort_values("_quick_score", ascending=False).head(self.config.max_candidates_per_row)

    @staticmethod
    def _text_similarity(a: Any, b: Any) -> float:
        aa, bb = safe_text(a), safe_text(b)
        if not aa or not bb:
            return 0.0
        return max(float(fuzz.ratio(aa, bb)), float(fuzz.token_set_ratio(aa, bb)))

    def _get_compatible_fx(
        self, row_a: pd.Series, row_b: pd.Series, fx_rates: Dict[Tuple[str, str], Dict[str, Any]]
    ) -> Optional[float]:
        curr_a, curr_b = safe_text(row_a.get("currency")), safe_text(row_b.get("currency"))
        if not curr_a or not curr_b:
            return None
        if curr_a == curr_b:
            return 1.0
        return self._get_fx_rate(curr_a, curr_b, fx_rates)

    @staticmethod
    def _get_fx_rate(
        currency_a: str, currency_b: str, fx_rates: Dict[Tuple[str, str], Dict[str, Any]]
    ) -> Optional[float]:
        if currency_a == currency_b:
            return 1.0
        direct = fx_rates.get((currency_a, currency_b))
        if direct:
            return float(direct["rate"])
        reverse = fx_rates.get((currency_b, currency_a))
        if reverse and reverse["rate"] != 0:
            return 1.0 / float(reverse["rate"])
        return None

    def _amount_close(self, a: float, b: float, fx_rate: Optional[float]) -> bool:
        comparable_b = b / fx_rate if (fx_rate and fx_rate > 0) else b
        return abs(a - comparable_b) <= max(self.config.amount_tolerance, abs(a) * 0.001)

    @staticmethod
    def _base_result(
        row_a: pd.Series, row_b: pd.Series, match_type: str, confidence: float
    ) -> Dict[str, Any]:
        return {
            "match_type": match_type,
            "confidence": round(float(confidence), 2),
            "source_a": row_a["source"],
            "source_a_row": int(row_a["source_row"]),
            "source_b": row_b["source"],
            "source_b_row": int(row_b["source_row"]),
            "date_a": row_a["date"].strftime("%Y-%m-%d"),
            "date_b": row_b["date"].strftime("%Y-%m-%d"),
            "amount_a": round(float(row_a["amount"]), 2),
            "amount_b": round(float(row_b["amount"]), 2),
            "currency_a": row_a.get("currency", ""),
            "currency_b": row_b.get("currency", ""),
            "reference_a": row_a.get("reference", ""),
            "reference_b": row_b.get("reference", ""),
            "description_a": row_a.get("description", ""),
            "description_b": row_b.get("description", ""),
        }

    def _record_match(
        self,
        row_a: pd.Series,
        row_b: pd.Series,
        score: Dict[str, Any],
        match_type: str,
        results: List[Dict[str, Any]],
        fx_rate: Optional[float],
    ):
        result = self._base_result(row_a, row_b, match_type, score["score"])
        result.update(
            {
                "amount_score": score["amount_score"],
                "date_score": score["date_score"],
                "reference_score": score["reference_score"],
                "description_score": score["description_score"],
                "direction_score": score["direction_score"],
                "amount_difference": score["amount_difference"],
                "fx_rate": safe_round(fx_rate, 8),
                "reason": self._build_reason(score, fx_rate),
            }
        )
        results.append(result)

    @staticmethod
    def _build_reason(score: Dict[str, Any], fx_rate: Optional[float]) -> str:
        reasons = []
        if score["amount_score"] >= 95:
            reasons.append("amounts align")
        elif score["amount_score"] >= 80:
            reasons.append("amounts are very close")

        if score["date_score"] >= 90:
            reasons.append("dates align")
        elif score["date_score"] >= 60:
            reasons.append("dates are within window")

        if score["reference_score"] >= 90:
            reasons.append("references strongly match")
        if score["description_score"] >= 90:
            reasons.append("descriptions strongly match")

        if fx_rate is not None and abs(fx_rate - 1.0) > 1e-12:
            reasons.append(f"FX conversion supported at {fx_rate:.8f}")

        return ("; ".join(reasons) + ".") if reasons else "composite transaction evidence supports match."

    def _build_summary(
        self,
        a: pd.DataFrame,
        b: pd.DataFrame,
        matches: pd.DataFrame,
        unmatched_a: pd.DataFrame,
        unmatched_b: pd.DataFrame,
        schemas_a: List[Dict[str, Any]],
        schemas_b: List[Dict[str, Any]],
        fx_rates: Dict[Tuple[str, str], Dict[str, Any]],
        fee_profiles: Sequence[Dict[str, Any]],
    ) -> Dict[str, Any]:
        match_counts = {}
        if not matches.empty and "match_type" in matches.columns:
            match_counts = {str(k): int(v) for k, v in matches["match_type"].value_counts().to_dict().items()}

        fx_summary = {}
        for pair, info in fx_rates.items():
            fx_summary[f"{pair[0]}->{pair[1]}"] = {
                key: safe_round(value, 8) if isinstance(value, (float, int)) else value
                for key, value in info.items()
            }

        return {
            "generated_at": datetime.now(timezone.utc).isoformat(),
            "source_a_transactions": int(len(a)),
            "source_b_transactions": int(len(b)),
            "matched_transactions_or_groups": int(len(matches)),
            "unmatched_source_a": int(len(unmatched_a)),
            "unmatched_source_b": int(len(unmatched_b)),
            "unmatched_a_rows": _serialize_df_rows(unmatched_a),
            "unmatched_b_rows": _serialize_df_rows(unmatched_b),
            "match_types": match_counts,
            "match_rate_source_a_percent": round(100.0 * len(matches) / max(len(a), 1), 2),
            "fx_rates_inferred": fx_summary,
            "fee_profiles_learned": list(fee_profiles),
            "schema_detection": {
                "source_a": schemas_a,
                "source_b": schemas_b,
            },
        }


def dataframe_for_output(df: pd.DataFrame) -> pd.DataFrame:
    """Remove internal object columns before writing CSV."""
    if df is None:
        return pd.DataFrame()

    result = df.copy()
    if "_transaction" in result.columns:
        result = result.drop(columns=["_transaction"])

    for col in result.columns:
        if pd.api.types.is_datetime64_any_dtype(result[col]):
            result[col] = result[col].dt.strftime("%Y-%m-%d")

    return result


def json_safe(value: Any) -> Any:
    """Recursively make arbitrary values JSON serializable."""
    if isinstance(value, dict):
        return {str(k): json_safe(v) for k, v in value.items()}
    if isinstance(value, (list, tuple)):
        return [json_safe(v) for v in value]
    if isinstance(value, pd.Timestamp):
        return value.isoformat()
    if isinstance(value, float):
        return None if (math.isnan(value) or math.isinf(value)) else value
    if isinstance(value, (pd.Series, pd.DataFrame)):
        return value.to_dict()
    return value


def write_results(result: Dict[str, Any], output_dir: Union[str, Path] = "reconciliation_output") -> Path:
    # NOTE: no print() here. Writing to stdout would ignore --silent and can
    # corrupt output that Laravel reads from the process.
    out = Path(output_dir).resolve()
    out.mkdir(parents=True, exist_ok=True)

    matches = dataframe_for_output(result["matches"])
    unmatched_a = dataframe_for_output(result["unmatched_a"])
    unmatched_b = dataframe_for_output(result["unmatched_b"])

    matches.to_csv(out / "reconciliation_matches.csv", index=False)
    unmatched_a.to_csv(out / "reconciliation_unmatched_a.csv", index=False)
    unmatched_b.to_csv(out / "reconciliation_unmatched_b.csv", index=False)

    summary = json_safe(result["summary"])
    with open(out / "reconciliation_summary.json", "w", encoding="utf-8") as handle:
        json.dump(summary, handle, indent=2, ensure_ascii=False)

    if "exceptions" in result:
        with open(out / "reconciliation_exceptions.json", "w", encoding="utf-8") as handle:
            json.dump(json_safe(result["exceptions"]), handle, indent=2, ensure_ascii=False)

    return out


def build_argument_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Smart financial reconciliation engine."
    )
    parser.add_argument("source_a", help="First CSV/XLSX/XLS/PDF statement.")
    parser.add_argument("source_b", help="Second CSV/XLSX/XLS/PDF statement.")
    parser.add_argument("--output", default="reconciliation_output", help="Directory for output files.")
    parser.add_argument("--date-window", type=int, default=3, help="Max date diff in days for matching.")
    parser.add_argument("--amount-tolerance", type=float, default=0.01, help="Same-currency amount tolerance.")
    parser.add_argument("--threshold", type=float, default=82.0, help="Automatic match confidence threshold.")
    parser.add_argument("--max-split-items", type=int, default=5, help="Max items in a split payment cluster.")

    # Dashboard configuration flags
    parser.add_argument("--start-date", default=None, help="Start date filter (YYYY-MM-DD).")
    parser.add_argument("--end-date", default=None, help="End date filter (YYYY-MM-DD).")
    parser.add_argument("--variance-tolerance", type=float, default=0.00, help="Allowed amount variance tolerance.")
    parser.add_argument("--settlement-buffer", type=int, default=2, help="Settlement date buffer in days.")
    parser.add_argument("--fuzzy-threshold", type=float, default=85.0, help="Fuzzy match confidence threshold.")

    # Active ML Pipeline Toggles
    parser.add_argument("--enable-fuzzy", action="store_true", default=False, help="Enable fuzzy matching pass.")
    parser.add_argument("--enable-fx", action="store_true", default=False, help="Enable dynamic FX estimation pass.")
    parser.add_argument("--enable-split", action="store_true", default=False, help="Enable split payment detection pass.")
    parser.add_argument("--enable-gateway", action="store_true", default=False, help="Enable gateway fee inference pass.")

    # Optional audit overrides
    parser.add_argument("--audit-entity-column", default=None, help="Counterparty column name used by the exception audit.")
    parser.add_argument("--audit-direction-column", default=None, help="DEBIT/CREDIT column name used by the exception audit.")
    parser.add_argument("--audit-trace-pattern", default=None, help="Regex used to extract the trace ID from the reference.")
    parser.add_argument(
        "--raw-unmatched",
        action="store_true",
        default=False,
        help="Report the engine's raw unmatched rows instead of categorized exceptions.",
    )

    # Silent execution flag
    parser.add_argument(
        "--silent",
        action="store_true",
        default=False,
        help="Suppress stdout output",
    )
    return parser


def load_and_preprocess(file_path: str) -> pd.DataFrame:
    """Load file safely across supported extensions and apply optional classification."""
    loader = FileLoader()
    frames = loader.load(file_path)

    if not frames:
        raise ValueError(f"Unable to read tabular data from {file_path}")

    df = pd.concat(frames, ignore_index=True)

    # Run dynamic column classification & renaming if available
    if SelfLearningColumnClassifier is not None:
        try:
            classifier = SelfLearningColumnClassifier(
                taxonomy_dir="config/taxonomy",
                file_name=file_path
            )
            df = classifier.classify_and_rename(df)
        except Exception:
            pass

    return df


def _serialize_df_rows(df: pd.DataFrame) -> List[Dict[str, Any]]:
    if df is None or df.empty:
        return []
    # Strip internal object columns (like _transaction) before JSON serialization
    clean_df = dataframe_for_output(df)
    return json.loads(clean_df.to_json(orient="records", date_format="iso"))


def main() -> int:
    parser = build_argument_parser()
    args = parser.parse_args()

    # Determine toggle values
    has_explicit_toggles = any(arg.startswith("--enable-") for arg in sys.argv)

    enable_fuzzy = args.enable_fuzzy if has_explicit_toggles else True
    enable_fx = args.enable_fx if has_explicit_toggles else True
    enable_split = args.enable_split if has_explicit_toggles else True
    enable_gateway = args.enable_gateway if has_explicit_toggles else True

    # Combine tolerances and settlement windows
    effective_tolerance = max(args.amount_tolerance, args.variance_tolerance)
    effective_date_window = args.settlement_buffer if "--settlement-buffer" in sys.argv else args.date_window
    effective_threshold = args.fuzzy_threshold if "--fuzzy-threshold" in sys.argv else args.threshold

    config = EngineConfig(
        amount_tolerance=max(0.0, effective_tolerance),
        date_window_days=max(0, effective_date_window),
        match_threshold=clamp(effective_threshold, 0.0, 100.0),
        max_split_items=max(2, args.max_split_items),
        start_date=args.start_date,
        end_date=args.end_date,
        variance_tolerance=args.variance_tolerance,
        settlement_buffer=args.settlement_buffer,
        fuzzy_threshold=args.fuzzy_threshold,
        enable_fuzzy=enable_fuzzy,
        enable_fx=enable_fx,
        enable_split=enable_split,
        enable_gateway=enable_gateway,
        audit_entity_column=args.audit_entity_column,
        audit_direction_column=args.audit_direction_column,
        audit_trace_pattern=args.audit_trace_pattern,
        report_exceptions_as_unmatched=not args.raw_unmatched,
    )

    try:
        # 1. Preprocess files in-memory without overwriting source files on disk
        df_a = load_and_preprocess(args.source_a)
        df_b = load_and_preprocess(args.source_b)

        # 2. Instantiate engine
        engine = SmartReconEngine(config)

        # 3. Run reconciliation directly on preprocessed DataFrames
        result = engine.reconcile(
            df_a,
            df_b,
            source_a_name=Path(args.source_a).name,
            source_b_name=Path(args.source_b).name,
        )
        output_dir = write_results(result, args.output)

        summary = result["summary"]

        if not args.silent:
            print("\nSmart Reconciliation completed successfully.")
            print("-" * 55)
            print(f"Source A transactions : {summary['source_a_transactions']}")
            print(f"Source B transactions : {summary['source_b_transactions']}")
            print(f"Matched transactions  : {summary['matched_transactions_or_groups']}")
            print(f"Exceptions A          : {summary['unmatched_source_a']}")
            print(f"Exceptions B          : {summary['unmatched_source_b']}")
            print(f"Total exceptions      : {summary['exception_total']}")
            print(f"Match rate A          : {summary['match_rate_source_a_percent']}%")
            print(f"Output directory      : {output_dir.resolve()}")

            if summary.get("exception_counts"):
                print("\nException breakdown:")
                for kind, count in summary["exception_counts"].items():
                    print(f"  {kind}: {count}")
                print(f"  (timing shifts ignored: {summary['timing_shift_count']})")

            if summary["match_types"]:
                print("\nMatch types:")
                for match_type, count in summary["match_types"].items():
                    print(f"  {match_type}: {count}")

            if summary["fx_rates_inferred"]:
                print("\nInferred FX rates:")
                for pair, info in summary["fx_rates_inferred"].items():
                    print(f"  {pair}: {info['rate']:.8f} (confidence {info['confidence']:.1f})")

            if summary["fee_profiles_learned"]:
                profiles = summary["fee_profiles_learned"]
                print(f"\nLearned gateway fee profiles ({len(profiles)} fee bands detected):")
                for profile in profiles[:5]:
                    print(f"  • {profile['fee_percent'] * 100:.2f}% ({profile['observations']} observations)")
                if len(profiles) > 5:
                    print(f"  ... and {len(profiles) - 5} other minor fee profile patterns.")

            print("\nFiles written:")
            print(f"  {output_dir / 'reconciliation_matches.csv'}")
            print(f"  {output_dir / 'reconciliation_unmatched_a.csv'}")
            print(f"  {output_dir / 'reconciliation_unmatched_b.csv'}")
            print(f"  {output_dir / 'reconciliation_summary.json'}")
            print(f"  {output_dir / 'reconciliation_exceptions.json'}")

        return 0

    except Exception as exc:
        print(f"\nReconciliation failed: {type(exc).__name__}: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())