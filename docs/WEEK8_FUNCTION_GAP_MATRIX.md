# WEEK8_FUNCTION_GAP_MATRIX

| Nhóm | Chức năng | Trạng thái trước Tuần 8 | Route/file hiện có | Việc cần làm | Kết quả sau Tuần 8 |
|---|---|---|---|---|---|
| AUTH/session | Login/logout/session hash | Đã có M1 và test | `AuthController`, `M1AuthRoleSmokeTest` | Regression | PASS qua M1 regression |
| RBAC/ownership | Admin/host/guest/seller role | Đã có một phần | middleware `role`, policies | Mở rộng seller/host ownership | Thêm seller CRUD ownership, availability ownership |
| Homestay | Admin CRUD | Đã có M1 | `Admin\HomestayController` | Regression | Giữ nguyên, M1 pass |
| Room | Host CRUD | Đã có M2 | `Host\RoomController` | Vá `homestay_id` update theo owner | Đã ràng buộc `exists` theo owner |
| Room availability/calendar | Host xem/cập nhật theo ngày | Thiếu UI/service | `room_availability` model | Thêm UI/controller/service/test | Đã thêm `/chu-homestay/availability` và route update |
| Seasonal price | Host quản lý khoảng giá | Thiếu UI CRUD cơ bản | `SeasonalPrice` model | Thêm tạo/xóa giá mùa | Đã thêm `/chu-homestay/seasonal-prices` |
| Search/filter | Public search | Đã có M2 | `SearchController` | Regression | M2 pass |
| Booking | Tạo booking chống double booking | Đã có M2 | `BookingService` | Regression | M2 pass |
| Booking history/cancel | Guest xem/hủy booking | Thiếu | `bookings` schema | Thêm history/cancel + rollback `units_sold` | Đã thêm `/tai-khoan/bookings` |
| Local products | Public browse/detail/recommend | Đã có M3 | `LocalProductController` | Regression | M3 pass |
| Seller CRUD | Seller quản lý sản phẩm | Thiếu | `local_products` schema | Thêm index/create/edit/update + ownership | Đã thêm `/nguoi-ban/products` |
| Product order/cart | Guest tạo order | Chưa có | `product_orders`, `product_order_items` | Tạo order từ DB price, transaction, stock | Đã thêm order service/controller |
| Order state | Seller đổi trạng thái hợp lệ | Chưa có | `product_orders.status` | State transition guard | Đã thêm pending -> confirmed -> packing -> delivered và cancel rule |
| Payment test mode | Mock payment booking | Chưa có | `payments` schema | Success/fail/duplicate guard | Đã thêm `/tai-khoan/bookings/{booking}/payments/mock` |
| Experiences | Browse/detail/booking | Đã có M3 | `ExperienceController` | Regression | M3 pass |
| Experience booking | Capacity conflict 409 | Đã có M3 | `ExperienceBookingService` | Regression | M3 pass |
| Reviews | Completed booking only | Thiếu route/service | `reviews` schema | Thêm guest_to_host review | Đã thêm completed-only + rating validation |
| Notifications | Schema có nhưng UI thiếu | Chưa làm | `notifications` table | Sau Must | BLOCKED/OUT OF SCOPE Week 8 sau Must |
| Loyalty | Schema có nhưng UI thiếu | Chưa làm | `loyalty_transactions` table | Sau Must | BLOCKED/OUT OF SCOPE Week 8 sau Must |
| Admin/reporting | Dashboard analytics | Đã có M3 | `DashboardController` | Regression | M3 pass |
| Python recommender/analytics | FastAPI + fallback | Đã có M3 | `PythonDataService`, `python-service` | Regression | Python/M3 pass |
| Audit/logging | Booking status logs có | Một phần | `booking_status_logs`, Laravel logs | Rà OWASP | Không thêm route giả; logging Python/conflict giữ nguyên |
