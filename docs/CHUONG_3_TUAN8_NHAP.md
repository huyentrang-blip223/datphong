# CHƯƠNG 3 - THIẾT KẾ HỆ THỐNG

## 3.1 Kiến trúc tổng thể
Hệ thống ĐT-07 được tổ chức theo mô hình modular monolith trên Laravel 9, dùng MySQL làm cơ sở dữ liệu chính và một dịch vụ nội bộ FastAPI/Python cho recommendation và analytics. Laravel xử lý xác thực, phân quyền, giao diện Blade, nghiệp vụ booking, order, payment test mode và review. Python service được gọi nội bộ qua HTTP có token, timeout, retry, cache và fallback.

Luồng request chính: người dùng gửi HTTP request đến Laravel route; middleware xác thực và role kiểm tra quyền; controller nhận request và gọi FormRequest để validate; service thực thi business rule trong transaction khi cần; Eloquent đọc/ghi MySQL; view Blade hoặc JSON response trả kết quả.

Các sơ đồ nguồn nằm trong `docs/diagrams/`: context, component, sitemap, guest/host/seller/admin flow và Python integration.

## 3.2 Thiết kế CSDL vật lý
CSDL vật lý theo schema Week 4 có 21 bảng: `users`, `homestays`, `homestay_verifications`, `rooms`, `amenities`, `room_amenities`, `room_availability`, `seasonal_prices`, `bookings`, `booking_status_logs`, `payments`, `local_products`, `homestay_products`, `product_orders`, `product_order_items`, `experiences`, `experience_bookings`, `reviews`, `loyalty_transactions`, `notifications`, `audit_logs`.

Các quyết định quan trọng: `homestays` tách khỏi `rooms`; `room_availability` lưu tồn theo ngày với `units_total`, `units_held`, `units_sold`, `price_override`; `seasonal_prices` lưu khoảng giá theo ngày, không ghi đè `rooms.base_price`; `bookings` có `booking_status_logs`; sản phẩm địa phương dùng `local_products`, `homestay_products`, `product_orders`, `product_order_items`; review có unique theo `booking_id + direction`.

Ràng buộc chính gồm PK/FK, CHECK cho rating, số khách, giá, tồn phòng và trạng thái enum. Index phục vụ tìm kiếm homestay, lọc phòng, availability theo ngày, booking theo khách/phòng/trạng thái, sản phẩm theo seller/status và order theo buyer. Schema có view `vw_public_homestays`, `vw_room_daily_inventory` và trigger guard cho availability.

## 3.3 Thiết kế API/route
Web routes chính:
- Public: `/`, `/tim-kiem`, `/tim-kiem/ket-qua`, `/san-pham-dia-phuong`, `/trai-nghiem`, `/healthz`.
- Auth: `/dang-nhap`, `/dang-xuat`.
- Guest: `POST /bookings`, `/tai-khoan/bookings`, mock payment, review, `/tai-khoan/orders`.
- Host: `/chu-homestay/bang-dieu-khien`, room CRUD, availability, seasonal prices.
- Seller: `/nguoi-ban/products`, `/nguoi-ban/orders`.
- Admin: `/quan-tri/bang-dieu-khien`, admin homestay CRUD.

FastAPI endpoints: `/healthz`, `/recommend/local-products/{product_id}`, `/analytics/seasonality`, `/analytics/top-homestays`, `/cache/refresh`. Laravel maps validation errors to 422, permission failures to 403, unauthenticated web flows to login redirect, availability/stock conflicts to 409 for JSON, and successful creation to 201 where applicable.

## 3.4 Điều hướng và tổ chức trang
Master layout chứa header, role-aware nav, theme toggle, content area and academic footer. Guest flow đi từ search/product/experience đến booking/order/payment/review. Host flow đi từ dashboard đến room CRUD, availability calendar and seasonal pricing. Seller flow đi từ product CRUD đến order status. Admin flow đi từ dashboard analytics đến homestay CRUD.

## 3.5 UX/UI
Week 8 bổ sung hai theme thật: `light` và `forest`. Token dùng CSS custom properties trong `public/css/app.css`, theme lưu trong `localStorage`, có fallback theo `prefers-color-scheme`. Các thành phần dùng chung gồm card, grid, form, button, alert, responsive table wrapper và focus outline. Responsive mục tiêu 390/768/1440 được hỗ trợ bằng CSS breakpoint, nav wrap và form grid tự chuyển một cột.

## 3.6 Bảo mật
Auth dùng Laravel guard và password hash. CSRF vẫn bật cho web form. Middleware `role` bảo vệ route theo admin/guest/host/seller. Policy và FormRequest bảo vệ ownership: host chỉ sửa phòng/lịch/giá của homestay mình; seller chỉ sửa sản phẩm mình; guest chỉ xem/hủy booking/order của mình. Query dùng Eloquent/query builder; Blade escaping mặc định giảm XSS. Python service dùng token nội bộ, timeout/retry/cache/fallback. OWASP review vòng 1 nằm trong `docs/OWASP_TOP10_WEEK8.md`.

## 3.7 Python
Python service phục vụ gợi ý sản phẩm bằng content-based filtering: ghép name, origin_place, description, homestay and province; vector hóa TF-IDF unigram/bigram; tính cosine similarity; loại sản phẩm hiện tại; ưu tiên điểm tương đồng và khoảng cách giá. Analytics gồm seasonality và top homestays. ETL làm sạch CSV sản phẩm địa phương, xuất clean CSV và stats JSON. Laravel tích hợp qua `PythonDataService`, có cache 30 phút và fallback Eloquent khi Python lỗi.

## 3.8 Kết luận Chương 3
Thiết kế Week 8 giữ đúng phạm vi homestay cộng đồng, mở rộng phần còn thiếu của M1/M2/M3 bằng host calendar, seasonal pricing, seller CRUD, product order, payment test mode, booking history/cancel và review. Hệ thống vẫn chưa hoàn thiện notifications/loyalty UI, browser matrix bị chặn do thiếu Node/Playwright, và Lighthouse chưa chạy được trong môi trường hiện tại.
