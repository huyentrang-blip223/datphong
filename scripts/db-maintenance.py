"""Byte-safe MySQL backup and isolated restore verification; no production restore."""
import argparse
import datetime as dt
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys
import uuid

ROOT = Path(__file__).resolve().parents[1]


def executable(name):
    installed = Path(r'C:\Program Files\MySQL\MySQL Server 8.0\bin') / f'{name}.exe'
    value = str(installed) if name != 'php' and installed.is_file() else (shutil.which(name) or str(Path(r'C:\xampp') / ('php/php.exe' if name == 'php' else f'mysql/bin/{name}.exe')))
    if not Path(value).is_file():
        raise RuntimeError(f'{name} executable not found')
    return value


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('action', choices=['backup', 'restore-test'])
    parser.add_argument('--output-dir', default=str(ROOT / 'backups'))
    parser.add_argument('--backup-file')
    args = parser.parse_args()
    config = json.loads(subprocess.check_output([executable('php'), str(ROOT / 'scripts/db-connection.php')], cwd=ROOT))
    db = config['database']
    if not re.fullmatch(r'[A-Za-z0-9_]+', db):
        raise RuntimeError('Unsupported database name')
    env = os.environ.copy()
    env['MYSQL_PWD'] = config.get('password') or ''
    common = [f'--host={config["host"]}', f'--port={config["port"]}', f'--user={config["username"]}', '--default-character-set=utf8mb4']
    mysql = [executable('mysql'), *common, '--batch', '--skip-column-names']

    def run(command, **kwargs):
        result = subprocess.run(command, env=env, stderr=subprocess.PIPE, **kwargs)
        if result.returncode:
            raise RuntimeError(f'{Path(command[0]).name} failed (exit {result.returncode}); check local permissions/connectivity')
        return result

    def query(sql):
        return run([*mysql, '-e', sql], stdout=subprocess.PIPE).stdout.decode('utf-8').strip().splitlines()

    tables = query(f'SHOW TABLES FROM `{db}`')
    if any(not re.fullmatch(r'[A-Za-z0-9_]+', name) for name in tables):
        raise RuntimeError('Unsupported table identifier')

    def counts(schema):
        return {table: int(query(f'SELECT COUNT(*) FROM `{schema}`.`{table}`')[0]) for table in tables}

    if args.action == 'backup':
        output = Path(args.output_dir).resolve()
        output.mkdir(parents=True, exist_ok=True)
        path = output / f'{db}-{dt.datetime.now():%Y%m%d-%H%M%S}-{uuid.uuid4().hex[:6]}.sql'
        run([executable('mysqldump'), *common, '--single-transaction', '--routines', '--triggers', '--no-tablespaces', '--set-gtid-purged=OFF', f'--result-file={path}', db], stdout=subprocess.DEVNULL)
        if path.stat().st_size == 0:
            raise RuntimeError('Empty backup')
        print(json.dumps({'action': 'backup', 'file': str(path), 'bytes': path.stat().st_size}))
    else:
        if not args.backup_file:
            raise RuntimeError('--backup-file is required')
        path = Path(args.backup_file).resolve()
        with path.open('rb') as stream:
            for line in stream:
                if re.match(rb'^\s*(?:USE\s|(?:CREATE|DROP)\s+DATABASE\s)', line, re.I):
                    raise RuntimeError('Backup contains database switching; refused')
        temporary = 'dt07_restore_test_' + uuid.uuid4().hex
        if not re.fullmatch(r'dt07_restore_test_[a-f0-9]{32}', temporary) or temporary == db:
            raise RuntimeError('Unsafe restore target')
        expected = counts(db)
        query(f'CREATE DATABASE `{temporary}` CHARACTER SET utf8mb4')
        try:
            with path.open('rb') as stream:
                run([*mysql, temporary], stdin=stream, stdout=subprocess.DEVNULL)
            restored = counts(temporary)
            if expected != restored:
                raise RuntimeError('Restored row counts differ from source (source may have changed since backup)')
            print(json.dumps({'action':'restore-test','status':'PASS','tables':len(restored),'row_counts':restored,'source_database_unchanged':True}))
        finally:
            query(f'DROP DATABASE `{temporary}`')


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(f'ERROR: {type(error).__name__}: {error}', file=sys.stderr)
        sys.exit(1)
