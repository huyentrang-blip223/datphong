# Triển khai DT07 lên Render

Gói này giữ Laravel 9, MySQL 8 và FastAPI. Chưa có dịch vụ Render nào được tạo hoặc thanh toán. `render.yaml` khai báo hai web service Free; không tự tạo database trả phí.

## Những gì cần trước khi triển khai

1. Tài khoản Render kết nối GitHub repo `huyentrang-blip223/datphong`. Có thể dùng tích hợp Render trong cuộc trò chuyện sau khi người dùng cài/kết nối.
2. Database **MySQL 8** bên ngoài Render: hostname, port, database, username/password và CA nếu nhà cung cấp yêu cầu TLS. Không dùng địa chỉ MySQL localhost trên máy cá nhân. Không chuyển sang Render Postgres vì schema/truy vấn hiện tại dùng MySQL.
3. Khóa Laravel mới, ổn định: chạy `php artisan key:generate --show` ở máy local rồi nhập kết quả vào biến APP_KEY của Render. Không commit khóa vào Git.
4. Email/mật khẩu admin ban đầu (tối thiểu 12 ký tự) nhập qua dashboard Render. Không dùng tài khoản demo công khai để quản trị website Internet.

## Thao tác Render

1. Đẩy mã đã kiểm tra lên GitHub. Trong Render chọn **New -> Blueprint**, kết nối repo và dùng `render.yaml`.
2. Dịch vụ `dt07-homestay`: nhập APP_KEY, DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD, BOOTSTRAP_ADMIN_EMAIL và BOOTSTRAP_ADMIN_PASSWORD. Nếu cần CA, thêm file bí mật CA và đặt MYSQL_ATTR_SSL_CA là đường dẫn file đó. Tránh log thông tin đăng nhập.
3. Dịch vụ `dt07-python`: DT07_DB_URL theo dạng `mysql+pymysql://USER:PASSWORD@HOST:PORT/DATABASE` (URL encode mật khẩu), SERVICE_TOKEN tự chọn. Nếu dùng TLS, thêm MYSQL_SSL_CA trỏ tới file CA.
4. Điền PY_SERVICE_URL của Laravel bằng URL thực của dịch vụ Python; PY_SERVICE_TOKEN phải giống SERVICE_TOKEN của Python. Hai web service Free không nhận kết nối nội bộ nên dùng URL HTTPS công khai của Python.
5. Runtime tự dùng RENDER_EXTERNAL_URL làm APP_URL. Nginx/PHP-FPM phục vụ port 8080; supervisor quản lý scheduler. Blueprint không cần Node/npm.
6. Khi database còn trống, bootstrap tạo schema nền và trigger từ schema hiện tại (bỏ lệnh DROP/CREATE/USE DATABASE), chạy migration và tạo admin một lần. Nếu database có tài khoản, startup không đặt lại mật khẩu hay xóa dữ liệu. Nếu database không trống mà thiếu users, startup dừng để tránh ghi đè.
7. DEPLOY_DEMO_DATA mặc định false. Chỉ đặt true khi khởi tạo database demo trống để nạp dataset du lịch; seeder này tạo tài khoản thử đã biết mật khẩu. Sau đó dùng quản trị người dùng để khóa tài khoản thử trước khi chia sẻ rộng rãi.

`/healthz` chỉ trả tình trạng; không chứa cấu hình bí mật. Có thể kiểm tra chứng thư/headers sau khi biết URL thật:

```powershell
python scripts/check-https.py https://URL-THAT-CUA-BAN.onrender.com
```

## Giới hạn Free cần xử lý

- Render Free ngủ sau khoảng 15 phút không hoạt động; scheduler trong container cũng dừng khi dịch vụ ngủ. Luồng tìm kiếm/booking/checkout vẫn dọn hold quá hạn khi truy cập trở lại. Muốn tác vụ chạy suốt ngày cần dịch vụ không ngủ; không tuyên bố Free chạy scheduler 24/7.
- File ảnh/giấy tờ upload trên Free có thể mất khi restart/redeploy. Để giữ đầy đủ chức năng upload, nâng dịch vụ Laravel lên gói hỗ trợ disk và gắn disk tại `/var/www/html/storage`. Mã startup tạo lại thư mục/symlink. Việc nâng gói cần người dùng xem và chấp nhận chi phí trong Render. Không lưu database vào filesystem của Free.
- Hai dịch vụ dùng chung hạn mức Free của workspace. Kiểm tra hạn mức hiện hành trước khi sử dụng lâu dài.

## Email quên mật khẩu trên Render

Free chặn cổng SMTP 25/465/587. Đã thêm mailer HTTP Resend để gửi qua HTTPS: đặt MAIL_MAILER=resend, RESEND_API_KEY và MAIL_FROM_ADDRESS là địa chỉ được nhà cung cấp xác thực. Cấu hình mặc định log chỉ phục vụ kiểm tra ban đầu, chưa gửi email tới người dùng. Khóa và xác thực sender/domain cần tài khoản của người dùng.

## VNPay sandbox

Nhập VNPAY_TMN_CODE và VNPAY_SECRET_KEY của merchant **đã được phê duyệt**, và VNPAY_RETURN_URL là URL website thật cộng `/api/v1/payment/vn-pay-callback`. Đăng ký IPN HTTPS với VNPay tại `/api/v1/payment/vn-pay-ipn`.

Kiểm tra trực tiếp ngày 2026-10-18 bằng cấu hình local hiện có đã tới máy chủ sandbox, nhưng trang cổng phản hồi **Website này chưa được phê duyệt**. Không có giao dịch thành công hoặc hoàn tiền bên ngoài nào được tuyên bố. Mã đã hỗ trợ hoàn tiền/query sandbox có xác thực phản hồi; timeout giữ trạng thái unknown để đối soát, không tự gửi yêu cầu tài chính lặp.

## Trạng thái kiểm chứng

- Khởi tạo database tạm mới: PASS, 30 bảng/view và hai trigger; startup lần hai giữ một admin, không tạo trùng. Database tạm được xóa sau kiểm tra.
- Docker build: chưa chạy được, Docker Desktop báo unable to start. Chưa tuyên bố container build thành công.
- Chưa có URL Render, MySQL ngoài hoặc kết nối tài khoản Render được xác nhận; chưa deploy công khai và chưa xác thực chứng thư HTTPS của website.

Tham khảo chính thức: [Laravel Docker trên Render](https://render.com/docs/deploy-php-laravel-docker), [Free limits](https://render.com/docs/free), [MySQL trên Render](https://render.com/docs/deploy-mysql), [Resend API](https://resend.com/docs/api-reference/emails/send-email).
