"""Read-only UI smoke checks; login uses the existing academic demo accounts.

Install playwright separately (or into .appdata/qa). Start Laravel, then run:
python e2e/verify_completed_flows.py --base-url http://127.0.0.1:8010
"""
import argparse
import json
import os
from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
if (ROOT / '.appdata/qa').exists():
    sys.path.insert(0, str(ROOT / '.appdata/qa'))
from playwright.sync_api import sync_playwright


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--base-url', default='http://127.0.0.1:8010')
    parser.add_argument('--browser-executable', default=r'C:\Program Files\Google\Chrome\Application\chrome.exe')
    parser.add_argument('--output', default=str(ROOT / '.appdata/ui-checks'))
    args = parser.parse_args()
    output = Path(args.output)
    output.mkdir(parents=True, exist_ok=True)
    results = []
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(executable_path=args.browser_executable, headless=True)
        for role, paths in {
            'admin': ['/quan-tri/bang-dieu-khien', '/quan-tri/verifications', '/quan-tri/users', '/quan-tri/users/create'],
            'host': ['/chu-homestay/homestays/create', '/chu-homestay/experience-bookings'],
            'guest': ['/tai-khoan/experience-bookings', '/tai-khoan/bookings', '/tai-khoan/profile', '/tai-khoan/cart'],
        }.items():
            context = browser.new_context()
            page = context.new_page()
            errors = []
            page.on('pageerror', lambda error: errors.append(str(error)))
            page.goto(args.base_url + '/dang-nhap')
            page.locator('input[name=email]').fill(os.getenv(f'DT07_{role.upper()}_EMAIL', f'{role}@dt07.test'))
            page.locator('input[name=password]').fill(os.getenv('DT07_DEMO_PASSWORD', 'Dt07@2026!'))
            page.locator('button[type=submit]').click()
            page.wait_for_load_state('networkidle')
            assert '/dang-nhap' not in page.url, f'{role} demo login failed'
            for path in paths:
                for width in (390, 768, 1440):
                    page.set_viewport_size({'width': width, 'height': 960})
                    response = page.goto(args.base_url + path)
                    assert response.status == 200, (path, response.status)
                    page.wait_for_load_state('networkidle')
                    if role == 'admin' and 'bang-dieu-khien' in path:
                        page.wait_for_function("document.querySelectorAll('[data-analytics-chart] select').length === 2")
                        monthly = page.locator('[data-analytics-chart=monthly]')
                        monthly.locator('select').select_option('units_sold')
                        assert monthly.locator('svg').get_attribute('aria-label').endswith('Số đêm đã bán')
                    assert page.evaluate('document.documentElement.scrollWidth <= window.innerWidth + 1'), (path, width, 'overflow')
                    assert not errors, errors
                    page.screenshot(path=str(output / f'{role}-{path.rsplit("/", 1)[-1]}-{width}.png'), full_page=True)
                    results.append({'role': role, 'path': path, 'width': width, 'status': 'PASS'})
            context.close()
        browser.close()
    (output / 'results.json').write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding='utf-8')
    print(f'{len(results)} UI checks passed in Chromium/Chrome; screenshots: {output}')


if __name__ == '__main__':
    main()
