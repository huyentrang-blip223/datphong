import os
from typing import Annotated

from dotenv import load_dotenv
from fastapi import Depends, FastAPI, Header, HTTPException

from app.analytics import seasonality, top_homestays
from app.recommender import recommend_local_products, refresh_cache

load_dotenv()

app = FastAPI(title="DT-07 M3 Python Data Service")


def require_token(x_service_token: Annotated[str | None, Header()] = None) -> None:
    expected = os.getenv("SERVICE_TOKEN", "")
    if expected and x_service_token == expected:
        return
    raise HTTPException(status_code=401, detail="Invalid service token")


@app.get("/healthz")
def healthz() -> dict[str, str]:
    return {"status": "ok"}


@app.get("/recommend/local-products/{product_id}", dependencies=[Depends(require_token)])
def recommend(product_id: int, k: int = 6):
    return recommend_local_products(product_id, max(1, min(k, 20)))


@app.get("/analytics/seasonality", dependencies=[Depends(require_token)])
def analytics_seasonality():
    return seasonality()


@app.get("/analytics/top-homestays", dependencies=[Depends(require_token)])
def analytics_top_homestays(limit: int = 10):
    return top_homestays(max(1, min(limit, 30)))


@app.post("/cache/refresh", dependencies=[Depends(require_token)])
def cache_refresh():
    return refresh_cache()
