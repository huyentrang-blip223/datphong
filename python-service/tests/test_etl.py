from pathlib import Path

from app.etl_clean import run


def test_etl_dry_run_writes_clean_output(tmp_path: Path):
    input_path = tmp_path / "raw.csv"
    output_path = tmp_path / "clean.csv"
    input_path.write_text(
        "Name,Origin Place,Description,Unit,Price,Stock Qty\n"
        " Tea  , Lam Dong , Local tea , goi, 10000 , 5\n"
        " Tea, Lam Dong, Duplicate, goi, 10000, 5\n"
        "Bad,N/A,Negative,goi,-1,3\n",
        encoding="utf-8",
    )

    stats = run(str(input_path), str(output_path), dry_run=True)

    assert output_path.exists()
    assert stats["rows_before"] == 3
    assert stats["rows_after"] == 1
