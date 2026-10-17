"""Verify a real deployment using the OS CA store, never --insecure."""
import argparse
import json
import urllib.request
from urllib.parse import urlparse

parser=argparse.ArgumentParser()
parser.add_argument('url')
args=parser.parse_args()
url=args.url.rstrip('/')
if urlparse(url).scheme!='https': raise SystemExit('An HTTPS URL is required')
with urllib.request.urlopen(url,timeout=20) as response:
    if urlparse(response.url).scheme!='https': raise SystemExit('Unexpected HTTP downgrade')
    missing=[name for name in ['Strict-Transport-Security','X-Content-Type-Options','X-Frame-Options','Referrer-Policy','Content-Security-Policy'] if not response.headers.get(name)]
    if missing: raise SystemExit('Missing security headers: '+', '.join(missing))
with urllib.request.urlopen(url+'/healthz',timeout=20) as response:
    health=json.load(response)
    if health.get('application')!='ok' or health.get('database')!='ok': raise SystemExit('Application/database health is not OK')
print(json.dumps({'url':url,'certificate_validation':'PASS','security_headers':'PASS','application_database_health':'PASS'}))
