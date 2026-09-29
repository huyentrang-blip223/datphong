# ĐT-07 — Project Week 5 / Mốc M1

Mục tiêu theo kế hoạch: khung hệ thống chạy được, đăng nhập, phân quyền theo vai trò, bố cục dùng chung và CRUD tối thiểu 01 thực thể (`homestays`).

## Cách ghép vào Laravel 11
1. Tạo project: `composer create-project laravel/laravel:^11.0 backend`
2. Chép các file trong thư mục `backend/` của gói này vào project tương ứng.
3. Dùng schema Tuần 4 (`01_schema_DT07.sql`) làm nguồn dữ liệu hoặc chuyển thành migrations.
4. Chép `.env.week5.example` thành `.env`, sửa mật khẩu MySQL, chạy `php artisan key:generate`.
5. Tạo bảng session: `php artisan session:table && php artisan migrate` (nếu dùng migration cho schema).
6. Đăng ký alias middleware `role` theo `bootstrap/app_middleware_snippet.php` vào `bootstrap/app.php`.
7. Chạy `php artisan db:seed --class=DemoAccountSeeder`.
8. Chạy `php artisan serve --port=8000`.

Tài khoản demo: mật khẩu chung `Dt07@2026!` (chỉ dùng local/dev, đổi trước go-live).
- admin@dt07.test — admin
- host@dt07.test — host
- guest@dt07.test — guest
- seller@dt07.test — seller

## Minh chứng M1 phải chụp trên máy nhóm
- Login thành công bằng admin.
- Host gọi trực tiếp `/quan-tri/bang-dieu-khien` nhận 403.
- Trang quản trị homestay: danh sách + tạo + sửa.
- DevTools/Application cho cookie session (HttpOnly, SameSite; Secure khi HTTPS).
- Bản ghi `users.password_hash` có tiền tố bcrypt/argon2id.
- Git log/commit của tuần 5.

Không dùng ảnh mockup làm bằng chứng runtime; ảnh cần lấy từ bản chạy thật.
