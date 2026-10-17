from app import db


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
