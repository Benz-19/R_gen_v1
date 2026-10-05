import json
import logging
import re
import sys
from pathlib import Path

import pandas as pd

logger = logging.getLogger("ReconAgent.Classifier")


class SelfLearningColumnClassifier:

    def __init__(
        self, taxonomy_dir: str = "config/taxonomy", file_name: str = "unknown"
    ):
        self.taxonomy_dir = Path(taxonomy_dir)
        self.taxonomy_dir.mkdir(parents=True, exist_ok=True)
        self.file_name = file_name

        self.files = {
            "amount": self.taxonomy_dir / "amount_aliases.json",
            "date": self.taxonomy_dir / "date_aliases.json",
            "reference": self.taxonomy_dir / "reference_aliases.json",
        }

        self.dictionaries = {
            key: self._load_dictionary(filepath)
            for key, filepath in self.files.items()
        }

        # NEW:
        # Separate file for automatically learned aliases.
        # Example:
        # {
        #     "value_day": "date",
        #     "trace_ref": "reference"
        # }
        self.learned_file = self.taxonomy_dir / "learned_aliases.json"
        self.learned_aliases = self._load_learned_aliases()

    def _load_dictionary(self, filepath: Path) -> set:
        if filepath.exists():
            with open(filepath, "r") as f:
                return set(json.load(f))
        return set()

    # NEW
    def _load_learned_aliases(self) -> dict:
        """Load automatically learned column aliases."""
        if self.learned_file.exists():
            try:
                with open(self.learned_file, "r") as f:
                    data = json.load(f)

                if isinstance(data, dict):
                    return data

            except (json.JSONDecodeError, OSError) as e:
                logger.warning(
                    f"[Self-Learning Engine] Could not load learned aliases: {e}"
                )

        return {}

    # NEW
    def _save_learned_alias(self, alias: str, category: str):
        """Save a newly learned column alias for future classification."""
        self.learned_aliases[alias] = category

        with open(self.learned_file, "w") as f:
            json.dump(self.learned_aliases, f, indent=2)

        logger.info(
            f"[Self-Learning Engine] Learned column alias: "
            f"'{alias}' -> '{category}'"
        )

    def _save_new_alias(self, category: str, alias: str):
        """Registers newly learned column word into the specific JSON dictionary file."""
        self.dictionaries[category].add(alias)

        with open(self.files[category], "w") as f:
            json.dump(
                sorted(list(self.dictionaries[category])),
                f,
                indent=2
            )

        logger.info(
            f"[Self-Learning Engine] Auto-registered new "
            f"'{category}' column alias: '{alias}'"
        )

    # NEW
    def _is_date_sample(self, sample: pd.Series) -> bool:
        """
        Determine whether a sample of values represents dates.

        Handles common date separators such as:
        - /
        - -
        - .
        - \

        Also handles mixed date formats where possible.
        """

        if sample.empty:
            return False

        valid_dates = 0
        total_values = 0

        for value in sample:
            if pd.isna(value):
                continue

            value = str(value).strip()

            if not value:
                continue

            total_values += 1

            # Date-like values should contain at least one separator
            # or look like a compact date/datetime.
            looks_date_like = bool(
                re.search(r"[\-/\\.]", value)
                or re.search(r"\d{4}\d{2}\d{2}", value)
            )

            if not looks_date_like:
                continue

            try:
                parsed = pd.to_datetime(
                    value,
                    errors="raise",
                    format="mixed"
                )

                if not pd.isna(parsed):
                    valid_dates += 1

            except (ValueError, TypeError):
                continue

        if total_values == 0:
            return False

        return (valid_dates / total_values) >= 0.85

    def classify_and_rename(self, df: pd.DataFrame) -> pd.DataFrame:
        mapped_columns = {}
        unrecognized_headers = []

        # Phase 1: Dictionary Matching
        for col in df.columns:
            clean_col = re.sub(
                r"[^a-zA-Z0-9_]",
                "",
                str(col)
            ).lower().strip()

            matched = False

            # NEW:
            # Check automatically learned aliases first.
            if clean_col in self.learned_aliases:
                category = self.learned_aliases[clean_col]

                # Only use valid categories.
                if category in self.dictionaries:
                    mapped_columns[category] = col
                    matched = True

            # Existing predefined dictionary matching remains unchanged.
            if not matched:
                for category, aliases in self.dictionaries.items():
                    if clean_col in aliases:
                        mapped_columns[category] = col
                        matched = True
                        break

            if not matched:
                unrecognized_headers.append((col, clean_col))

        # Phase 2: Heuristic Classification for Unknown Columns
        for original_col, clean_col in unrecognized_headers:
            sample_data = df[original_col].dropna().head(50)

            if sample_data.empty:
                continue

            guessed_category = self._apply_heuristic_rules(
                sample_data,
                mapped_columns
            )

            if guessed_category:
                mapped_columns[guessed_category] = original_col

                # Existing behavior:
                self._save_new_alias(
                    guessed_category,
                    clean_col
                )

                # NEW:
                # Also remember the mapping separately.
                self._save_learned_alias(
                    clean_col,
                    guessed_category
                )

        # Phase 3: Validation & Error Reporting
        missing_required = [
            req
            for req in ["amount", "date"]
            if req not in mapped_columns
        ]

        if missing_required:
            failed_cols = [c[0] for c in unrecognized_headers]

            # Output structured JSON payload via stderr
            # for Laravel to capture & store in DB
            db_log_payload = {
                "event": "UNCLASSIFIED_COLUMN_ERROR",
                "file_name": self.file_name,
                "missing_required": missing_required,
                "unclassified_headers": failed_cols,
            }

            sys.stderr.write(
                f"DB_LOG_PAYLOAD:{json.dumps(db_log_payload)}\n"
            )

            raise ValueError(
                f"Unable to process file structure. "
                f"ReconAgent could not safely detect the "
                f"'{', '.join(missing_required)}' column(s). "
                f"Unclassified headers found: "
                f"{', '.join(failed_cols)}."
            )

        rename_map = {
            v: k
            for k, v in mapped_columns.items()
        }

        return df.rename(columns=rename_map)

    def _apply_heuristic_rules(
        self,
        sample: pd.Series,
        existing_matches: dict
    ) -> str:
        """Applies data analysis rules to infer column identity when dictionary match fails."""

        if "amount" not in existing_matches:
            numeric_matches = sum(
                1
                for val in sample
                if re.match(
                    r"^-?[$€£₦]?\s*[\d,]+(\.\d+)?$",
                    str(val).strip()
                )
            )

            if (numeric_matches / len(sample)) >= 0.85:
                return "amount"

        if "date" not in existing_matches:

            # NEW:
            # Use safer date detection instead of:
            #
            # pd.to_datetime(sample, errors="raise")
            #
            # This avoids the pandas warning about format inference
            # and supports mixed/common date formats.
            if self._is_date_sample(sample):
                return "date"

        if "reference" not in existing_matches:
            if sample.nunique() / len(sample) > 0.7:
                return "reference"

        return None