from typing import Any

import pandas as pd

from app.db import get_connection


def seasonality() -> dict[str, list[dict[str, Any]]]:
    query = """
        SELECT
            DATE_FORMAT(stay_date, '%Y-%m') AS month,
            SUM(units_total) AS units_total,
            SUM(units_sold) AS units_sold,
            SUM(units_held) AS units_held
        FROM room_availability
        GROUP BY DATE_FORMAT(stay_date, '%Y-%m')
        ORDER BY month
        """
    connection = get_connection()
    try:
        with connection.cursor() as cursor:
            cursor.execute(query)
            frame = pd.DataFrame(cursor.fetchall()).fillna(0)
    finally:
        connection.close()
    rows = []
    for _, row in frame.iterrows():
        total = int(row["units_total"])
        sold = int(row["units_sold"])
        rows.append(
            {
                "month": str(row["month"]),
                "units_total": total,
                "units_sold": sold,
                "units_held": int(row["units_held"]),
                "occupancy_rate": round(sold / total, 4) if total else 0.0,
            }
        )
    return {"seasonality": rows}


def top_homestays(limit: int = 10) -> dict[str, list[dict[str, Any]]]:
    query = """
        SELECT
            h.id AS homestay_id,
            h.name AS homestay_name,
            h.province,
            COUNT(b.id) AS booking_count,
            COALESCE(SUM(b.total_amount), 0) AS revenue
        FROM homestays h
        JOIN rooms r ON r.homestay_id = h.id
        LEFT JOIN bookings b ON b.room_id = r.id
        GROUP BY h.id, h.name, h.province
        ORDER BY booking_count DESC, revenue DESC, h.name
        LIMIT %s
        """
    connection = get_connection()
    try:
        with connection.cursor() as cursor:
            cursor.execute(query, [limit])
            frame = pd.DataFrame(cursor.fetchall()).fillna(0)
    finally:
        connection.close()
    rows = []
    for _, row in frame.iterrows():
        rows.append(
            {
                "homestay_id": int(row["homestay_id"]),
                "homestay_name": str(row["homestay_name"]),
                "province": str(row["province"]),
                "booking_count": int(row["booking_count"]),
                "revenue": float(row["revenue"]),
            }
        )
    return {"top_homestays": rows}
