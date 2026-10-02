import argparse
import json
import logging
import re
from pathlib import Path

import pandas as pd

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")


def normalize_column(name: str) -> str:
    cleaned = re.sub(r"[^a-zA-Z0-9]+", "_", name.strip().lower())
    return cleaned.strip("_")


def clean_frame(frame: pd.DataFrame) -> tuple[pd.DataFrame, dict]:
    before = len(frame)
    frame = frame.copy()
    frame.columns = [normalize_column(column) for column in frame.columns]

    for column in frame.select_dtypes(include=["object"]).columns:
        frame[column] = frame[column].fillna("").astype(str).str.strip().str.replace(r"\s+", " ", regex=True)

    if "price" in frame.columns:
        frame["price"] = pd.to_numeric(frame["price"], errors="coerce").fillna(0)
        frame = frame[frame["price"] >= 0]

    if "stock_qty" in frame.columns:
        frame["stock_qty"] = pd.to_numeric(frame["stock_qty"], errors="coerce").fillna(0).astype(int)
        frame = frame[frame["stock_qty"] >= 0]

    duplicate_key = [column for column in ["name", "origin_place"] if column in frame.columns]
    if duplicate_key:
        frame = frame.drop_duplicates(subset=duplicate_key, keep="first")
    else:
        frame = frame.drop_duplicates()

    after = len(frame)
    stats = {
        "source_note": "synthetic / project seed",
        "rows_before": int(before),
        "rows_after": int(after),
        "rows_removed": int(before - after),
        "columns": list(frame.columns),
        "numeric_summary": frame.describe(include="number").to_dict(),
    }
    return frame, stats


def run(input_path: str, output_path: str, dry_run: bool = False) -> dict:
    source = Path(input_path)
    output = Path(output_path)
    frame = pd.read_csv(source)
    cleaned, stats = clean_frame(frame)

    logging.info("ETL rows before=%s after=%s removed=%s", stats["rows_before"], stats["rows_after"], stats["rows_removed"])

    output.parent.mkdir(parents=True, exist_ok=True)
    cleaned.to_csv(output, index=False)
    stats_path = output.with_suffix(".stats.json")
    stats_path.write_text(json.dumps(stats, indent=2, ensure_ascii=False), encoding="utf-8")

    if dry_run:
        logging.info("Dry run complete. Clean file and stats were written for inspection only.")

    print(json.dumps({"output": str(output), "stats": stats}, ensure_ascii=False))
    return stats


def main() -> None:
    parser = argparse.ArgumentParser(description="Clean DT-07 local product dataset.")
    parser.add_argument("--input", required=True)
    parser.add_argument("--output", default="data/clean/local_products_clean.csv")
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()
    run(args.input, args.output, args.dry_run)


if __name__ == "__main__":
    main()
