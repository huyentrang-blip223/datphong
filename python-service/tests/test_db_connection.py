from app import db


def test_database_url_can_be_built_from_discrete_container_variables(monkeypatch):
    monkeypatch.setenv('DT07_DB_HOST', 'mysql')
    monkeypatch.setenv('DT07_DB_PORT', '3307')
    monkeypatch.setenv('DT07_DB_NAME', 'tourism')
    monkeypatch.setenv('DT07_DB_USER', 'demo')
    monkeypatch.setenv('DT07_DB_PASSWORD', 'p@ss:/word')
    db.get_database_url.cache_clear()

    assert db.get_database_url() == 'mysql+pymysql://demo:p%40ss%3A%2Fword@mysql:3307/tourism'

    db.get_database_url.cache_clear()


def test_connection_decodes_url_credentials_and_passes_tls_context(monkeypatch):
    monkeypatch.setattr(db, 'get_database_url', lambda: 'mysql+pymysql://demo:p%40ss%3A%2Fword@db.example.test:3307/tourism')
    monkeypatch.delenv('MYSQL_SSL_CA', raising=False)
    monkeypatch.setattr(db.pymysql, 'connect', lambda **kwargs: kwargs)
    result = db.get_connection()
    assert result['password'] == 'p@ss:/word'
    assert result['host'] == 'db.example.test'
    assert result['port'] == 3307
    assert result['database'] == 'tourism'
    assert result['ssl'] is None
