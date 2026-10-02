import os

from fastapi.testclient import TestClient

from app import analytics, recommender
from app.main import app


os.environ["SERVICE_TOKEN"] = "test-token"
client = TestClient(app)


def test_healthz_returns_200():
    response = client.get("/healthz")
    assert response.status_code == 200
    assert response.json()["status"] == "ok"


def test_protected_endpoint_requires_token():
    response = client.get("/analytics/seasonality")
    assert response.status_code == 401


def test_recommendation_schema_and_excludes_current_product(monkeypatch):
    def fake_recommend(product_id: int, k: int = 6):
        return {
            "source": "python",
            "items": [{"product_id": 2, "name": "Tea", "score": 0.5}],
            "message": None,
        }

    monkeypatch.setattr(recommender, "recommend_local_products", fake_recommend)
    monkeypatch.setattr("app.main.recommend_local_products", fake_recommend)
    response = client.get("/recommend/local-products/1?k=6", headers={"X-Service-Token": "test-token"})
    assert response.status_code == 200
    body = response.json()
    assert "items" in body
    assert all(item["product_id"] != 1 for item in body["items"])


def test_analytics_endpoint_returns_json(monkeypatch):
    def fake_seasonality():
        return {"seasonality": [{"month": "2026-10", "units_total": 10, "units_sold": 2, "occupancy_rate": 0.2}]}

    monkeypatch.setattr(analytics, "seasonality", fake_seasonality)
    monkeypatch.setattr("app.main.seasonality", fake_seasonality)
    response = client.get("/analytics/seasonality", headers={"X-Service-Token": "test-token"})
    assert response.status_code == 200
    assert response.json()["seasonality"][0]["month"] == "2026-10"
