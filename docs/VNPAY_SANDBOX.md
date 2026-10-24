# Thanh toán thử VNPay

VNPay cho phòng và đơn sản phẩm sử dụng HMAC SHA512 và kiểm tra chữ ký, số tiền, trạng thái giao dịch khi nhận kết quả. Các lựa chọn thanh toán mô phỏng đã được gỡ theo yêu cầu mới; xem [thanh toán tiền mặt và VNPay](CASH_PAYMENTS.md).

## Cấu hình local

Thông tin merchant sandbox mới (mã website `BDU3W4CJ`) được cấu hình trong `backend/.env` ngày 2026-10-24. File này được Git bỏ qua; không chép khóa bí mật vào tài liệu hoặc mã nguồn.

```dotenv
VNPAY_ENABLED=true
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
2. Đặt phòng còn trống và chọn **Thanh toán VNPay** trước khi hết hạn giữ phòng.
3. Hoàn tất thanh toán thử trên VNPay; trình duyệt quay về trang thanh toán booking. Chỉ kết quả có chữ ký hợp lệ và khớp số tiền mới được ghi nhận.

Đơn sản phẩm cũng có nút VNPay khi chưa chọn tiền mặt. Callback hợp lệ chuyển giao dịch sang `paid` và đơn từ `pending` sang `confirmed`; callback lặp không tạo thanh toán hoặc thông báo trùng. VNPay cho trải nghiệm vẫn chưa được triển khai.

Khi triển khai Render, nhập khóa trong Environment của dịch vụ, đặt Return URL theo URL HTTPS thực tế và đăng ký IPN `/api/v1/payment/vn-pay-ipn` với VNPay. File `.env` local không được đẩy lên GitHub.

## Kết quả kiểm tra ngày 2026-10-24

- Đã tạo URL có checksum bằng cấu hình merchant `BDU3W4CJ` và gửi một yêu cầu đọc trang thanh toán đến VNPay sandbox. Sandbox trả HTTP 200, không còn thông báo terminal chưa được phê duyệt và có nội dung trang checkout. Yêu cầu probe không tạo booking/giao dịch trong cơ sở dữ liệu ứng dụng.
- `C:\xampp\php\php.exe artisan config:clear`: thành công.
- `C:\xampp\php\php.exe artisan test --filter="VnPay|vnpay"`: **9 passed**. Các bài kiểm thử callback dùng thông tin giả và chữ ký test; vẫn cần người dùng hoàn tất một giao dịch bằng thẻ sandbox để kiểm tra toàn bộ luồng trình duyệt/IPN bên ngoài.
- Trạng thái booking chỉ chuyển từ `holding` sang `confirmed` sau khi cả `vnp_ResponseCode` và `vnp_TransactionStatus` bằng `00`, chữ ký hợp lệ và số tiền khớp. Giao dịch lỗi được lưu `failed`; callback muộn không được phép khôi phục booking đã hủy/hết hạn.

Tham khảo giao thức: [tài liệu thanh toán VNPay](https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html).
