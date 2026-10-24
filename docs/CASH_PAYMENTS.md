# Thanh toán tiền mặt và VNPay

Theo yêu cầu mới, website chỉ hiển thị hai phương thức: **Thanh toán tiền mặt** và **Thanh toán VNPay**. Các nút thành công/thất bại giả, đường dẫn `/payments/mock`, dịch vụ xử lý thanh toán mô phỏng đã được gỡ.

## Tiền mặt

1. Khách đặt phòng/sản phẩm/trải nghiệm và chọn **Thanh toán tiền mặt** ở trang thanh toán. Phương thức được lưu nhưng chưa thu tiền. Không ghi thời điểm đã trả tiền và không cộng điểm khi mới chọn.
2. Phòng được xác nhận và chuyển tồn giữ chỗ sang tồn đã đặt; không bị hết hạn giữ phòng sau 15 phút nữa. Tiền phòng được trả trực tiếp cho chủ homestay khi nhận phòng. Sản phẩm trả khi nhận hàng, trải nghiệm trả trước khi tham gia.
3. Chủ homestay vào danh sách booking hoặc khách đăng ký trải nghiệm; người bán vào danh sách đơn hàng. Sau khi thực nhận đủ tiền, tích **Đã thực nhận đủ tiền mặt** rồi chọn **Xác nhận đã thu tiền**. Server kiểm tra quyền sở hữu và ngăn thu trùng. Khách không tự đánh dấu đã thanh toán.
4. Đơn hàng tiền mặt có thể đóng gói/giao hàng trước khi thu tiền. Điểm sản phẩm chỉ cộng sau cả giao hàng và thu tiền; điểm lưu trú cộng khi chủ homestay xác nhận nhận tiền.
5. Nếu hủy một giao dịch đã thu tiền mặt, chủ homestay/người bán trả tiền trực tiếp cho khách, sau đó xác nhận đã hoàn tiền ở cùng danh sách. Hủy đơn không tự đánh dấu đã hoàn tiền.

Các thao tác xác nhận nhận/trả tiền phải gửi `confirmed=1`; số tiền và trạng thái lấy từ server. Xác nhận có audit log ghi người thực hiện.

## VNPay

Thông tin bí mật VNPay được giữ trong `backend/.env` hoặc Render Environment, không đưa vào Git. Khi `VNPAY_ENABLED=false`, khách thấy **VNPay hiện chưa khả dụng**, nút bị vô hiệu hóa và endpoint tạo thanh toán từ chối yêu cầu. Merchant sandbox hiện tại đã vượt qua probe trang checkout nên Render cấu hình `VNPAY_ENABLED=true`.

Sau khi merchant hoạt động, đặt `VNPAY_ENABLED=true` rồi chạy `php artisan config:clear` trong `backend` để mở VNPay cho đặt phòng và đơn sản phẩm. Return URL local là `http://127.0.0.1:8000/api/v1/payment/vn-pay-callback`. Callback/IPN vẫn kiểm tra chữ ký, số tiền và trạng thái; không dùng kết quả giả. VNPay cho trải nghiệm chưa được triển khai nên vẫn bị vô hiệu hóa ở trang trải nghiệm.

## Kiểm chứng

- `C:\xampp\php\php.exe artisan config:clear`: thành công, cấu hình local đã dùng `VNPAY_ENABLED=false`.
- `C:\xampp\php\php.exe artisan test`: **99 passed**, 18.51 giây. Chạy lại `--filter=CashPaymentsTest` sau khi thêm kiểm tra biểu mẫu thu/hoàn tiền: **8 passed**.
- Kiểm tra cú pháp toàn bộ PHP thuộc app/routes/config/tests/lang: **170 tệp đạt**. `php artisan view:cache` thành công. `git diff --check` không có lỗi khoảng trắng. `php artisan route:list --path=cash` xác nhận 9 route tiền mặt.
- Chrome: **30 kiểm tra giao diện chung đạt** ở chiều rộng 390/768/1440, ảnh trong `.appdata/ui-checks`. Thử mở thêm trang thanh toán từ dữ liệu guest có sẵn chưa thực hiện được vì trang danh sách booking của tài khoản đó không có liên kết checkout; không ghi nhận 9 kiểm tra bổ sung là đạt. Các trang checkout và biểu mẫu thu/hoàn tiền đã được kiểm tra bằng feature tests với dữ liệu transaction riêng.
- `CashPaymentsTest` kiểm tra UI chỉ có hai lựa chọn, endpoint mô phỏng trả 404, VNPay chưa khả dụng bị chặn ở server, giữ tồn phòng với lựa chọn tiền mặt, quyền sở hữu, xác nhận thu/hoàn tiền, chống lặp, thu tiền khi giao hàng và điểm thưởng.
- Kiểm tra thủ công còn lại: chủ homestay/người bán thực nhận hoặc trả lại tiền ngoài hệ thống rồi xác nhận trên giao diện. Không tuyên bố đã thực hiện thu tiền hay giao dịch VNPay bên ngoài.

Không đổi schema, không xóa giao dịch cũ và không thay các lệnh trong `run.txt`.

## Changed files

- `backend/.env.example`
- `backend/.env.production.example`
- `backend/app/Http/Controllers/BookingLifecycleController.php`
- `backend/app/Http/Controllers/ExperienceBookingController.php`
- `backend/app/Http/Controllers/ExperienceBookingPaymentController.php`
- `backend/app/Http/Controllers/Host/BookingController.php`
- `backend/app/Http/Controllers/PaymentController.php`
- `backend/app/Http/Controllers/ProductOrderController.php`
- `backend/app/Http/Controllers/ProductOrderPaymentController.php`
- `backend/app/Http/Requests/MockPaymentRequest.php`
- `backend/app/Services/BookingLifecycleService.php`
- `backend/app/Services/BookingReportService.php`
- `backend/app/Services/CommercePaymentService.php`
- `backend/app/Services/ExperienceBookingService.php`
- `backend/app/Services/LoyaltyService.php`
- `backend/app/Services/PaymentService.php`
- `backend/app/Services/ProductOrderService.php`
- `backend/app/Services/VnPayPaymentService.php`
- `backend/config/services.php`
- `backend/resources/views/admin/payments/index.blade.php`
- `backend/resources/views/bookings/index.blade.php`
- `backend/resources/views/experiences/bookings.blade.php`
- `backend/resources/views/host/bookings/index.blade.php`
- `backend/resources/views/layouts/app.blade.php`
- `backend/resources/views/orders/index.blade.php`
- `backend/resources/views/payments/experience_booking.blade.php`
- `backend/resources/views/payments/product_order.blade.php`
- `backend/resources/views/payments/show.blade.php`
- `backend/routes/web.php`
- `backend/tests/Feature/CompletedFlowsTest.php`
- `backend/tests/Feature/M3PythonIntegrationTest.php`
- `backend/tests/Feature/VnPayGatewayTest.php`
- `backend/tests/Feature/Week8OrderPaymentReviewTest.php`
- `backend/tests/Feature/Week9FreezeReadinessTest.php`
- `docs/PROJECT_CONTEXT.md`
- `docs/RENDER_DEPLOYMENT.md`
- `backend/app/Http/Controllers/CashReceiptController.php`
- `backend/app/Http/Requests/CashPaymentRequest.php`
- `backend/app/Http/Requests/CashReceiptRequest.php`
- `backend/app/Services/CashPaymentService.php`
- `backend/lang/vi/payments.php`
- `backend/resources/views/payments/cash_receipt.blade.php`
- `backend/resources/views/payments/options.blade.php`
- `backend/tests/Feature/CashPaymentsTest.php`
- `docs/CASH_PAYMENTS.md`
- `docs/VNPAY_SANDBOX.md`
