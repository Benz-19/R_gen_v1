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

    def _load_dictionary(self, filepath: Path) -> set:
        if filepath.exists():
            with open(filepath, "r") as f:
                return set(json.load(f))
        return set()

    def _save_new_alias(self, category: str, alias: str):
        """Registers newly learned column word into the specific JSON dictionary file."""
        self.dictionaries[category].add(alias)
        with open(self.files[category], "w") as f:
            json.dump(sorted(list(self.dictionaries[category])), f, indent=2)
        logger.info(
            f"[Self-Learning Engine] Auto-registered new '{category}' column alias: '{alias}'"
        )

    def classify_and_rename(self, df: pd.DataFrame) -> pd.DataFrame:
        mapped_columns = {}
        unrecognized_headers = []

        # Phase 1: Dictionary Matching
        for col in df.columns:
            clean_col = re.sub(r"[^a-zA-Z0-9_]", "", str(col)).lower().strip()
            matched = False

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
                sample_data, mapped_columns
            )

            if guessed_category:
                mapped_columns[guessed_category] = original_col
                self._save_new_alias(guessed_category, clean_col)

        # Phase 3: Validation & Error Reporting
        missing_required = [
            req
            for req in ["amount", "date"]
            if req not in mapped_columns
        ]

        if missing_required:
            failed_cols = [c[0] for c in unrecognized_headers]

            # Output structured JSON payload via stderr for Laravel to capture & store in DB
            db_log_payload = {
                "event": "UNCLASSIFIED_COLUMN_ERROR",
                "file_name": self.file_name,
                "missing_required": missing_required,
                "unclassified_headers": failed_cols,
            }

            sys.stderr.write(f"DB_LOG_PAYLOAD:{json.dumps(db_log_payload)}\n")

            raise ValueError(
                f"Unable to process file structure. ReconAgent could not safely detect "
                f"the '{', '.join(missing_required)}' column(s). "
                f"Unclassified headers found: {', '.join(failed_cols)}."
            )

        rename_map = {v: k for k, v in mapped_columns.items()}
        return df.rename(columns=rename_map)

    def _apply_heuristic_rules(
        self, sample: pd.Series, existing_matches: dict
    ) -> str:
        """Applies data analysis rules to infer column identity when dictionary match fails."""
        if "amount" not in existing_matches:
            numeric_matches = sum(
                1
                for val in sample
                if re.match(
                    r"^-?[\$€£\₦]?\s*[\d,]+(\.\d+)?$", str(val).strip()
                )
            )
            if (numeric_matches / len(sample)) >= 0.85:
                return "amount"

        if "date" not in existing_matches:
            try:
                pd.to_datetime(sample, errors="raise")
                return "date"
            except Exception:
                pass

        if "reference" not in existing_matches:
            if sample.nunique() / len(sample) > 0.7:
                return "reference"

        return None