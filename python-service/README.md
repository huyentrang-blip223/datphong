# DT-07 Python Service

FastAPI service for Week 7 / M3.

## Features
- Content-based local product recommendations using TF-IDF and cosine similarity.
- Admin analytics for occupancy by month and top homestays.
- Rerunnable ETL/cleaning script for local product data.
- Header protection with `X-Service-Token` for data endpoints.

## Run
```bash
pip install -r requirements.txt
copy .env.example .env
uvicorn app.main:app --host 127.0.0.1 --port 8001
```

Set `SERVICE_TOKEN` in `.env` and use it from Laravel as `PY_SERVICE_TOKEN`.
