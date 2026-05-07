# XoSo AI (API)

API Laravel phục vụ dữ liệu kết quả xổ số, thống kê, dự đoán (Free/VIP), thanh toán và webhook.

Base path: `/api/v1/*`

## Các nhóm endpoint chính

- Auth: `POST /v1/login`, `POST /v1/register`, `GET /v1/me`, `POST /v1/logout`
- Result: `GET /v1/results` (có `is_multi_region`), `GET /v1/results/{date}`
- Stats: `GET /v1/stats`, `GET /v1/stats/{number}`, `GET /v1/number/most-frequent`
- Predictions: `GET /v1/predictions`, `GET /v1/predictions/yesterday`
- VIP: `GET /v1/vip/status`, `GET /v1/vip/upsell`, `POST /v1/vip/start-trial`, `GET /v1/vip/predictions*`
- Payments: `GET /v1/payments/plans`, `POST /v1/payments`, `GET /v1/payments/history`, `POST /v1/payments/{payment}/paid`
- Webhook: `GET/POST/PUT/DELETE /v1/api/webhooks`
- Admin (cần quyền `role=admin` hoặc `permission=developer`):
  - `GET/PUT /v1/admin/users*`
  - `GET/PUT /v1/admin/results*` (sửa tay theo ngày)

## Chạy local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Queue & Schedule

- Job crawl/update/stat/prediction được schedule trong [bootstrap/app.php](bootstrap/app.php)
- Cần chạy queue worker:

```bash
php artisan queue:work
```

## Webhook

- Event: `result.updated`
- Payload bao gồm `result.id`, `date`, `region`, `province_code`, `raw_data`

## Robots

API mặc định `Disallow: /` trong `public/robots.txt` để tránh bị index.
