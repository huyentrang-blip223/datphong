# Chatbot Gemini gợi ý phòng

Chatbot nổi trên giao diện thu thập số khách, địa điểm, ngày nhận/trả phòng, ngân sách tối đa mỗi đêm và mô tả sở thích tự do.

## Nguyên tắc dữ liệu

- Laravel lọc trước các phòng đang hoạt động thuộc homestay đã duyệt.
- Sức chứa, địa điểm, ngân sách và tồn phòng theo ngày được kiểm tra từ MySQL.
- Giá theo ngày giữ đúng thứ tự `price_override` -> seasonal price -> base price.
- Gemini chỉ xếp hạng tối đa ba ID trong danh sách ứng viên; ID không tồn tại bị backend loại bỏ.
- Khi Gemini lỗi, timeout hoặc hết quota, hệ thống trả gợi ý dự phòng từ database thay vì làm hỏng trang.
- Endpoint có CSRF, giới hạn 10 yêu cầu/phút và 100 yêu cầu/ngày theo IP.

## Cấu hình

```dotenv
GEMINI_ENABLED=true
GEMINI_API_KEY=<key-tu-Google-AI-Studio>
GEMINI_MODEL=gemini-3.5-flash
GEMINI_TIMEOUT=12
```

Key chỉ được nhập trong `.env` local hoặc Render Environment. Không đặt key trong JavaScript, Blade, `render.yaml` hay Git.

## Render

`render.yaml` khai báo `GEMINI_API_KEY` với `sync: false`. Sau khi sync Blueprint, nhập key vào service Laravel `dt07-homestay`, lưu bằng **Save, rebuild, and deploy**, rồi kiểm tra `/healthz` có `gemini: configured`.

## Kiểm tra thủ công

1. Mở website và nhấn **Gợi ý phòng AI** ở góc dưới bên phải.
2. Nhập số người và sở thích; thử một lần không có ngày ở.
3. Nhập đủ check-in/check-out để xác nhận chỉ phòng còn trống được trả về.
4. Nhấn **Xem phòng** và đối chiếu phòng, giá, sức chứa với trang chi tiết.
5. Kiểm tra Render Logs nếu phản hồi dùng `source=fallback`; không log API key.
