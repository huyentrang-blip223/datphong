(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('[data-room-chatbot-toggle]');
        const panel = document.querySelector('[data-room-chatbot-panel]');
        const close = document.querySelector('[data-room-chatbot-close]');
        const form = document.querySelector('[data-room-chatbot-form]');
        const status = document.querySelector('[data-room-chatbot-status]');
        const results = document.querySelector('[data-room-chatbot-results]');
        if (!toggle || !panel || !form || !status || !results) return;

        const setOpen = function (open) {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
            if (open) form.querySelector('[name="message"]').focus();
        };

        toggle.addEventListener('click', function () {
            setOpen(panel.hidden);
        });
        close.addEventListener('click', function () { setOpen(false); });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const submit = form.querySelector('button[type="submit"]');
            status.textContent = 'Đang đối chiếu phòng trống và sở thích của bạn...';
            results.replaceChildren();
            submit.disabled = true;

            try {
                const payload = Object.fromEntries(new FormData(form).entries());
                Object.keys(payload).forEach(function (key) {
                    if (payload[key] === '') delete payload[key];
                });
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });
                const body = await response.json();
                if (!response.ok) {
                    const validation = body.errors ? Object.values(body.errors).flat()[0] : null;
                    throw new Error(validation || body.message || 'Không thể lấy gợi ý lúc này.');
                }
                renderResponse(body.data);
            } catch (error) {
                status.textContent = error.message || 'Đã có lỗi, vui lòng thử lại.';
            } finally {
                submit.disabled = false;
            }
        });

        function renderResponse(data) {
            status.textContent = data.message;
            results.replaceChildren();
            (data.recommendations || []).forEach(function (room) {
                const card = document.createElement('article');
                card.className = 'room-chatbot-card';

                const image = document.createElement('img');
                image.src = room.image_url;
                image.alt = 'Ảnh ' + room.name;
                image.loading = 'lazy';
                image.addEventListener('error', function () { image.src = '/images/rooms/default.svg'; }, { once: true });

                const content = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = room.name + ' · ' + room.homestay;
                const meta = document.createElement('span');
                meta.textContent = room.location + ' · tối đa ' + room.max_guests + ' khách · ' + Number(room.price_per_night).toLocaleString('vi-VN') + ' đ/đêm';
                const reason = document.createElement('p');
                reason.textContent = room.reason;
                const link = document.createElement('a');
                link.className = 'btn btn-small';
                link.href = room.detail_url;
                link.textContent = 'Xem phòng';

                content.append(title, meta, reason, link);
                card.append(image, content);
                results.append(card);
            });
        }
    });
})();
