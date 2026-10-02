from dataclasses import dataclass
from typing import Any

import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

from app.db import get_connection


@dataclass
class RecommendationCache:
    products: pd.DataFrame | None = None
    matrix: Any = None
    vectorizer: TfidfVectorizer | None = None


_cache = RecommendationCache()


def load_products() -> pd.DataFrame:
    query = """
        SELECT
            lp.id AS product_id,
            lp.name,
            lp.origin_place,
            lp.description,
            lp.unit,
            lp.price,
            lp.status,
            GROUP_CONCAT(DISTINCT h.name ORDER BY h.name SEPARATOR ' ') AS homestay_names,
            GROUP_CONCAT(DISTINCT h.province ORDER BY h.province SEPARATOR ' ') AS provinces
        FROM local_products lp
        LEFT JOIN homestay_products hp ON hp.product_id = lp.id
        LEFT JOIN homestays h ON h.id = hp.homestay_id
        WHERE lp.status = 'published'
        GROUP BY lp.id, lp.name, lp.origin_place, lp.description, lp.unit, lp.price, lp.status
        ORDER BY lp.id
        """
    connection = get_connection()
    try:
        with connection.cursor() as cursor:
            cursor.execute(query)
            return pd.DataFrame(cursor.fetchall())
    finally:
        connection.close()


def refresh_cache() -> dict[str, int]:
    products = load_products()
    products = products.fillna("")
    text_columns = ["name", "origin_place", "description", "homestay_names", "provinces"]
    products["content_profile"] = products[text_columns].astype(str).agg(" ".join, axis=1)

    vectorizer = TfidfVectorizer(min_df=1, ngram_range=(1, 2))
    if products.empty or products["content_profile"].str.strip().eq("").all():
        matrix = None
    else:
        matrix = vectorizer.fit_transform(products["content_profile"])

    _cache.products = products
    _cache.vectorizer = vectorizer
    _cache.matrix = matrix

    return {"product_count": int(len(products))}


def recommend_local_products(product_id: int, k: int = 6) -> dict[str, Any]:
    if _cache.products is None:
        refresh_cache()

    products = _cache.products if _cache.products is not None else pd.DataFrame()
    if products.empty:
        return {"source": "python", "items": [], "message": "No published products available."}

    matches = products.index[products["product_id"] == product_id].tolist()
    if not matches:
        return {"source": "python", "items": [], "message": "Product not found or not published."}

    current_index = matches[0]
    current_price = float(products.loc[current_index, "price"])

    if _cache.matrix is None:
        fallback = products[products["product_id"] != product_id].copy()
        fallback["score"] = 0.0
    else:
        scores = cosine_similarity(_cache.matrix[current_index], _cache.matrix).flatten()
        fallback = products.copy()
        fallback["score"] = scores
        fallback = fallback[fallback["product_id"] != product_id]

    if current_price > 0:
        fallback["price_distance"] = (fallback["price"].astype(float) - current_price).abs()
    else:
        fallback["price_distance"] = 0

    fallback = fallback.sort_values(["score", "price_distance"], ascending=[False, True]).head(k)

    items = []
    for _, row in fallback.iterrows():
        items.append(
            {
                "product_id": int(row["product_id"]),
                "name": str(row["name"]),
                "origin_place": str(row["origin_place"]),
                "price": float(row["price"]),
                "score": round(float(row["score"]), 6),
            }
        )

    return {"source": "python", "items": items, "message": None}
