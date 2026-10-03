# Responsive and Theme Week 8

## Theme implementation
- Implemented two real themes: `light` and `forest`.
- Uses CSS custom properties in `backend/public/css/app.css`.
- Applies `data-theme` on `<html>`.
- Theme toggle persists with `localStorage` key `dt07-theme`.
- If no saved theme exists, initial theme follows `prefers-color-scheme`.
- Shared layout, header, footer, cards, tables, buttons, forms, alerts and focus styles use theme tokens.

## Responsive review targets

| Viewport | Routes intended for review | Result | Notes |
|---|---|---|---|
| 390x844 | `/`, `/tim-kiem`, `/san-pham-dia-phuong`, `/trai-nghiem`, `/chu-homestay/availability`, `/nguoi-ban/products` | STATIC REVIEW ONLY | CSS has mobile breakpoints, wrapped nav, table horizontal scroll. Browser execution blocked because Node/Playwright unavailable. |
| 768x1024 | `/san-pham-dia-phuong/{id}`, `/trai-nghiem/{id}`, `/tai-khoan/bookings`, `/tai-khoan/orders` | STATIC REVIEW ONLY | Forms use one-column below 800px. Browser execution blocked. |
| 1440x900 | `/quan-tri/bang-dieu-khien`, `/chu-homestay/seasonal-prices`, `/nguoi-ban/products` | STATIC REVIEW ONLY | Max content width 1120px. Browser execution blocked. |

## Accessibility checks applied in code
- Inputs are wrapped in labels.
- Focus outline uses `--focus` token.
- Theme colors avoid low-contrast text on primary buttons.
- Images are constrained with responsive CSS.
- Tables use `.table-wrap` to avoid destructive horizontal overflow on small screens.

## Execution limitation
Browser viewport screenshots were not executed because `node`, `npm`, and `npx` are not available in PATH on this environment.
