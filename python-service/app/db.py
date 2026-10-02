import os
from functools import lru_cache
from urllib.parse import quote_plus, urlparse

from dotenv import load_dotenv
import pymysql

load_dotenv()


@lru_cache(maxsize=1)
def get_database_url() -> str:
    return os.getenv("DT07_DB_URL", "mysql+pymysql://root:123456@127.0.0.1:3306/dt07_homestay")


def get_connection():
    parsed = urlparse(get_database_url())
    return pymysql.connect(
        host=parsed.hostname or "127.0.0.1",
        port=parsed.port or 3306,
        user=parsed.username or "root",
        password=parsed.password or "",
        database=(parsed.path or "/dt07_homestay").lstrip("/"),
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
    )


def mysql_url(user: str, password: str, host: str, port: int, database: str) -> str:
    return f"mysql+pymysql://{quote_plus(user)}:{quote_plus(password)}@{host}:{port}/{database}"
