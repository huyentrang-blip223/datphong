import os
import ssl
from functools import lru_cache
from urllib.parse import quote_plus, urlparse, unquote

from dotenv import load_dotenv
import pymysql

load_dotenv()


@lru_cache(maxsize=1)
def get_database_url() -> str:
    if os.getenv("DT07_DB_HOST"):
        return mysql_url(
            os.getenv("DT07_DB_USER", "root"),
            os.getenv("DT07_DB_PASSWORD", ""),
            os.environ["DT07_DB_HOST"],
            int(os.getenv("DT07_DB_PORT", "3306")),
            os.getenv("DT07_DB_NAME", "dt07_homestay"),
        )
    return os.getenv("DT07_DB_URL", "mysql+pymysql://root:123456@127.0.0.1:3306/dt07_homestay")


def get_connection():
    parsed = urlparse(get_database_url())
    ca_path = os.getenv("MYSQL_SSL_CA")
    tls = ssl.create_default_context(cafile=ca_path) if ca_path else None
    return pymysql.connect(
        host=parsed.hostname or "127.0.0.1",
        port=parsed.port or 3306,
        user=unquote(parsed.username or "root"),
        password=unquote(parsed.password or ""),
        database=(parsed.path or "/dt07_homestay").lstrip("/"),
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        ssl=tls,
    )


def mysql_url(user: str, password: str, host: str, port: int, database: str) -> str:
    return f"mysql+pymysql://{quote_plus(user)}:{quote_plus(password)}@{host}:{port}/{database}"
