# XoSo AI (Frontend)

Ứng dụng Nuxt hiển thị thống kê xổ số, kết quả hằng ngày, gợi ý số (Free/VIP) và trang quản trị nội bộ.

## Tính năng chính

- Trang chủ: preview số nổi bật, CTA nâng cấp VIP
- Dashboard: thống kê, tần suất, gợi ý số, lịch sử trúng hôm qua
- VIP: thanh toán/gói VIP, hiển thị dữ liệu VIP (heatmap 100 số, phân tích, chiến lược)
- Kết quả: xem kết quả theo ngày/miền
- Admin: quản lý user và sửa tay result theo ngày khi crawl sai

## Tech stack

- Nuxt 3 + TailwindCSS + Pinia
- Axios plugin với Bearer token (Sanctum token lưu cookie `sanctum_token`)

## Cấu hình môi trường

- `NUXT_PUBLIC_API_BASE_URL`: base URL API Laravel (mặc định `http://localhost:8989/api`)
- `NUXT_PUBLIC_SITE_URL`: base URL site để render canonical/SEO (mặc định `http://localhost:3000`)

## Chạy local

```bash
npm install
npm run dev
```

Mặc định chạy ở `http://localhost:3000`.

## Ghi chú SEO

- Các trang public có meta title/description + canonical
- Các trang nhạy cảm (`/admin`, `/dashboard`, `/login`, `/register`, API pages) được đặt `noindex`
- `public/robots.txt` chặn crawl các trang nhạy cảm
