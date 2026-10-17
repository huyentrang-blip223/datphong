import { createApp, h, ref } from './vendor/vue.runtime.esm-browser.prod.js';

for (const element of document.querySelectorAll('[data-analytics-chart]')) {
    const rows = JSON.parse(element.dataset.rows || '[]');
    const monthly = element.dataset.analyticsChart === 'monthly';
    const choices = monthly ? {occupancy_rate: 'Công suất (%)', units_sold: 'Số đêm đã bán'} : {booking_count: 'Số booking', revenue: 'Doanh thu (đ)'};
    createApp({
        setup() {
            const metric = ref(Object.keys(choices)[0]);
            return () => {
                const values = rows.map(row => Number(row[metric.value] || 0) * (metric.value === 'occupancy_rate' ? 100 : 1));
                const max = Math.max(1, ...values);
                return h('section', {class: 'card'}, [
                    h('label', [element.dataset.title, h('select', {value: metric.value, onChange: event => metric.value = event.target.value}, Object.entries(choices).map(([key, label]) => h('option', {value: key}, label)))]),
                    rows.length ? h('svg', {viewBox: `0 0 640 ${rows.length * 44 + 12}`, role: 'img', 'aria-label': `${element.dataset.title}: ${choices[metric.value]}`, style: 'width:100%;max-height:600px'}, rows.map((row, i) => {
                        const label = String(monthly ? row.month : row.homestay_name);
                        const value = values[i];
                        return h('g', {key: label}, [
                            h('title', `${label}: ${value.toLocaleString('vi-VN')}`),
                            h('text', {x: 0, y: i * 44 + 25, fill: 'var(--text)', 'font-size': 12}, label.length > 23 ? label.slice(0, 22) + '…' : label),
                            h('rect', {x: 180, y: i * 44 + 8, width: value / max * 330, height: 26, rx: 4, fill: 'var(--primary)'}),
                            h('text', {x: 520, y: i * 44 + 25, fill: 'var(--text)', 'font-size': 12}, Math.round(value * 100) / 100 + '')
                        ]);
                    })) : h('p', 'Chưa có dữ liệu cho biểu đồ.')
                ]);
            };
        }
    }).mount(element);
}
