<button class="room-chatbot-toggle" type="button" data-room-chatbot-toggle aria-controls="room-chatbot-panel" aria-expanded="false">
    <span aria-hidden="true">✦</span>
    <span>Gợi ý phòng AI</span>
</button>

<section id="room-chatbot-panel" class="room-chatbot-panel" data-room-chatbot-panel hidden aria-label="Trợ lý gợi ý phòng">
    <header class="room-chatbot-header">
        <div>
            <strong>Trợ lý chọn phòng</strong>
            <small>Gemini + dữ liệu phòng thực tế</small>
        </div>
        <button type="button" data-room-chatbot-close aria-label="Đóng chatbot">×</button>
    </header>

    <div class="room-chatbot-intro">
        Cho mình biết số người, nơi muốn đến và kiểu phòng bạn thích. Nếu có ngày ở, mình chỉ gợi ý phòng còn trống.
    </div>

    <form class="room-chatbot-form" data-room-chatbot-form action="{{ route('chatbot.rooms') }}">
        <div class="room-chatbot-grid">
            <label>Số người
                <input type="number" name="guests" min="1" max="20" value="2" required>
            </label>
            <label>Địa điểm
                <input type="text" name="location" maxlength="100" placeholder="Đà Lạt, Hội An...">
            </label>
            <label>Nhận phòng
                <input type="date" name="checkin" min="{{ now()->toDateString() }}">
            </label>
            <label>Trả phòng
                <input type="date" name="checkout" min="{{ now()->addDay()->toDateString() }}">
            </label>
            <label class="room-chatbot-wide">Ngân sách tối đa/đêm
                <input type="number" name="max_price" min="0" step="10000" placeholder="Ví dụ 800000">
            </label>
            <label class="room-chatbot-wide">Mong muốn và sở thích
                <textarea name="message" rows="3" maxlength="800" required placeholder="Ví dụ: Gia đình 4 người, thích yên tĩnh, gần thiên nhiên, có view đẹp và chỗ đỗ xe"></textarea>
            </label>
        </div>
        <button class="btn btn-accent room-chatbot-submit" type="submit">Tìm phòng phù hợp</button>
    </form>

    <div class="room-chatbot-status" data-room-chatbot-status aria-live="polite"></div>
    <div class="room-chatbot-results" data-room-chatbot-results></div>
</section>
