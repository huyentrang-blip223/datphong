# Thanh toán thử VNPay

VNPay cho phòng sử dụng HMAC SHA512 và kiểm tra chữ ký, số tiền, trạng thái giao dịch khi nhận kết quả. Các lựa chọn thanh toán mô phỏng đã được gỡ theo yêu cầu mới; xem [thanh toán tiền mặt và VNPay](CASH_PAYMENTS.md).

## Cấu hình local

Thông tin merchant trong ảnh mới của người dùng (mã website `WQL46SV0`) đã thay thế cấu hình trước trong `backend/.env` ngày 2026-10-18. File này được Git bỏ qua; không chép khóa bí mật vào tài liệu hoặc mã nguồn.

```dotenv
VNPAY_ENABLED=false
VNPAY_PAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_TMN_CODE=<ma-website-sandbox>
VNPAY_SECRET_KEY=<khoa-bi-mat-sandbox>
VNPAY_RETURN_URL=http://127.0.0.1:8000/api/v1/payment/vn-pay-callback
VNPAY_VERSION=2.1.0
VNPAY_COMMAND=pay
VNPAY_ORDER_TYPE=other
```

URL trả kết quả dùng cổng 8000 theo `run.txt`. Đường dẫn mẫu `http://localhost:8888/order/vnpay_return` thuộc cấu hình ứng dụng khác, không phải route của dự án này. Khi đổi host/cổng cần sửa URL tương ứng và chạy `php artisan config:clear` trong thư mục `backend`.

## Dùng thử

1. Chạy website theo `run.txt`, truy cập `http://127.0.0.1:8000` và đăng nhập tài khoản guest.
2. VNPay hiện bị vô hiệu hóa vì merchant chưa được phê duyệt. Sau khi merchant hoạt động, đặt `VNPAY_ENABLED=true`, chạy `php artisan config:clear`, đặt phòng còn trống và chọn **Thanh toán VNPay** trước khi hết hạn giữ phòng.
3. Hoàn tất thanh toán thử trên VNPay; trình duyệt quay về trang thanh toán booking. Chỉ kết quả có chữ ký hợp lệ và khớp số tiền mới được ghi nhận.

Khi triển khai Render, nhập khóa trong Environment của dịch vụ, đặt Return URL theo URL HTTPS thực tế và đăng ký IPN `/api/v1/payment/vn-pay-ipn` với VNPay. File `.env` local không được đẩy lên GitHub.

## Kết quả kiểm tra ngày 2026-10-18

- Đã tạo URL thanh toán từ cấu hình mới trong ảnh và gửi yêu cầu đến VNPay sandbox. Nhà cung cấp trả HTTP 200 ở trang lỗi với thông báo **The terminal (website) not approved** (mã tra cứu `zZzSGa1Ora`). Cần chủ tài khoản kiểm tra trạng thái kích hoạt mã website với VNPay. Chưa vào được bước chọn ngân hàng, chưa thực hiện giao dịch sandbox thành công.
- Dữ liệu phòng/booking của yêu cầu kiểm tra được tạo trong transaction và rollback sau khi tạo URL, không lưu booking thử vào dữ liệu đang dùng.
- `C:\xampp\php\php.exe artisan test --filter='VnPay|vnpay'`: **8 passed**. Các bài kiểm thử dùng thông tin giả và callback được ký trong test; kết quả này không thay thế việc thanh toán trên hệ thống VNPay.

Tham khảo giao thức: [tài liệu thanh toán VNPay](https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html).
