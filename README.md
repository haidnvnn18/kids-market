# KidsMarket

Nền tảng thương mại điện tử chuyên về sản phẩm trẻ em, xây dựng theo mô hình marketplace đa người bán. Hệ thống gồm hai thành phần chính: **REST API cho ứng dụng buyer** (mobile app / SPA) và **Seller Dashboard** (giao diện web quản trị dành cho người bán).

---

## Mục lục

1. [Tổng quan dự án](#tổng-quan-dự-án)
2. [Tech Stack](#tech-stack)
3. [Kiến trúc dự án](#kiến-trúc-dự-án)
4. [Tính năng chính](#tính-năng-chính)
5. [Luồng hoạt động](#luồng-hoạt-động)
6. [Database Schema](#database-schema)
7. [Cài đặt môi trường local](#cài-đặt-môi-trường-local)
8. [Cấu hình biến môi trường](#cấu-hình-biến-môi-trường)
9. [Chạy project](#chạy-project)
10. [Build & Deploy](#build--deploy)
11. [API Reference](#api-reference)
12. [Database Migration & Seed](#database-migration--seed)
13. [Ví dụ sử dụng API](#ví-dụ-sử-dụng-api)
14. [Lưu ý phát triển & mở rộng](#lưu-ý-phát-triển--mở-rộng)

---

## Tổng quan dự án

KidsMarket là sàn thương mại điện tử dành cho sản phẩm trẻ em, nơi nhiều nhà cung cấp (supplier/seller) có thể đăng bán hàng, và người mua (buyer) có thể tìm kiếm, thêm giỏ hàng, đặt hàng và đánh giá sản phẩm.

**Bài toán giải quyết:**
- Cung cấp REST API chuẩn JSON cho ứng dụng buyer (mobile/SPA) xử lý toàn bộ vòng đời mua hàng.
- Cung cấp giao diện web quản trị (Seller Dashboard) để người bán quản lý cửa hàng, sản phẩm, đơn hàng và đánh giá.
- Hỗ trợ cả giá bán lẻ và giá bán sỉ trên cùng một biến thể sản phẩm.
- Hỗ trợ đăng nhập qua mạng xã hội (Google, Facebook, Apple).
- Giao diện seller hỗ trợ đa ngôn ngữ (Tiếng Việt / Tiếng Trung Giản thể).

---

## Tech Stack

| Thành phần | Công nghệ |
|---|---|
| Backend framework | Laravel 12 (PHP 8.2+) |
| Web server | Nginx (Alpine) |
| PHP runtime | PHP-FPM 8.3 |
| Database | MySQL 8.0 |
| Frontend bundler | Vite + Node.js 20 |
| Seller UI | Blade templates |
| Authentication | Custom JWT (HMAC-SHA256, không dùng Sanctum/Passport) |
| Containerization | Docker + Docker Compose |
| DB management (dev) | phpMyAdmin |
| Testing | PHPUnit 11 |
| Code style | Laravel Pint |
| Dev tooling | Laravel Pail, Laravel Sail |

---

## Kiến trúc dự án

```
KidsMarket/
├── docker/
│   ├── nginx/default.conf          # Nginx config: route tới PHP-FPM
│   └── php/Dockerfile              # PHP 8.3-FPM + Node.js 20 + Composer
├── docker-compose.yml              # Services: app, nginx, mysql, phpmyadmin
└── src/                            # Laravel application root
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   │   ├── Api/            # Buyer REST API controllers
    │   │   │   │   ├── Seller/     # Seller REST API controllers (JSON)
    │   │   │   │   ├── AuthController.php
    │   │   │   │   ├── ProductController.php
    │   │   │   │   ├── CartController.php
    │   │   │   │   ├── OrderController.php
    │   │   │   │   ├── ReviewController.php
    │   │   │   │   ├── ShopController.php
    │   │   │   │   ├── ProvinceController.php
    │   │   │   │   ├── DistrictController.php
    │   │   │   │   └── ShippingAddressController.php
    │   │   │   └── Seller/         # Seller Web Dashboard controllers (Blade)
    │   │   │       ├── AuthController.php
    │   │   │       ├── DashboardController.php
    │   │   │       ├── ProductController.php
    │   │   │       ├── OrderController.php
    │   │   │       ├── ReviewController.php
    │   │   │       ├── ShopController.php
    │   │   │       └── PasswordResetController.php
    │   │   ├── Middleware/
    │   │   │   ├── AuthToken.php           # JWT auth bắt buộc (buyer)
    │   │   │   ├── AuthTokenOptional.php   # JWT auth tùy chọn (buyer)
    │   │   │   ├── AuthTokenSeller.php     # JWT auth bắt buộc (seller API)
    │   │   │   ├── SellerMiddleware.php    # Session auth cho seller web
    │   │   │   └── SetSellerLocale.php     # Set ngôn ngữ từ session
    │   │   └── Requests/           # Form Request validation
    │   ├── Models/                 # Eloquent models
    │   └── Providers/
    │       └── AppServiceProvider.php  # Rate limiter definitions
    ├── bootstrap/
    │   └── app.php                 # Middleware alias registration, route loading
    ├── config/                     # Laravel config files
    ├── database/
    │   ├── migrations/             # 24 migration files
    │   └── seeders/                # 11 seeder classes
    ├── lang/
    │   ├── vi/seller.php           # Chuỗi Tiếng Việt cho Seller UI
    │   └── zh/seller.php           # Chuỗi Tiếng Trung cho Seller UI
    ├── resources/views/seller/     # Blade templates cho Seller Dashboard
    ├── routes/
    │   ├── api.php                 # API routes (/api/*)
    │   ├── seller.php              # Web routes (/seller/*)
    │   └── web.php                 # General web routes
    └── tests/                      # PHPUnit tests
```

### Vai trò từng thành phần quan trọng

| File/Thư mục | Vai trò |
|---|---|
| `app/Http/Controllers/Api/` | Xử lý request từ buyer frontend, trả về JSON |
| `app/Http/Controllers/Api/Seller/` | Seller REST API (dùng cho tích hợp mobile/SPA của seller) |
| `app/Http/Controllers/Seller/` | Seller Web Dashboard — server-side render với Blade |
| `app/Http/Middleware/AuthToken.php` | Tự giải mã và xác thực JWT, gắn `auth_user` vào request |
| `app/Providers/AppServiceProvider.php` | Định nghĩa toàn bộ rate limiter cho auth endpoints |
| `bootstrap/app.php` | Điểm khởi động app, đăng ký middleware alias, load route files |
| `routes/api.php` | Tất cả API routes cho buyer và seller API |
| `routes/seller.php` | Web routes cho Seller Dashboard (Blade) |
| `lang/vi/seller.php` | File i18n tiếng Việt cho Seller UI |
| `lang/zh/seller.php` | File i18n tiếng Trung cho Seller UI |

---

## Tính năng chính

### Buyer API (`/api/*`)
- **Xác thực:** Đăng ký, đăng nhập (username/email + mật khẩu), đăng nhập mạng xã hội (Google/Facebook/Apple), kiểm tra tên đăng nhập trùng.
- **Sản phẩm:** Danh sách (filter theo category, supplier, từ khóa), tìm kiếm autocomplete, chi tiết sản phẩm, theo dõi lượt xem, toggle like/unlike.
- **Gợi ý sản phẩm:** Trang chủ gợi ý sản phẩm (hỗ trợ personalization khi đã đăng nhập).
- **Giỏ hàng:** Xem và cập nhật giỏ hàng cá nhân.
- **Đơn hàng:** Tạo đơn hàng từ biến thể sản phẩm (hỗ trợ mua sỉ/lẻ), xem danh sách đơn, xem chi tiết, hủy đơn. Filter theo status, ngày, keyword.
- **Đánh giá:** Xem danh sách đánh giá theo sản phẩm (kèm thống kê điểm), gửi đánh giá (mỗi user chỉ đánh giá 1 lần/sản phẩm).
- **Cửa hàng:** Xem thông tin shop, xem danh sách sản phẩm của shop.
- **Địa chỉ giao hàng:** CRUD địa chỉ giao hàng, đặt địa chỉ mặc định.
- **Tỉnh/Huyện:** Danh sách tỉnh thành, danh sách quận huyện theo tỉnh.

### Seller Dashboard (Web — `/seller/*`)
- **Auth:** Đăng nhập, đăng ký, quên mật khẩu, đặt lại mật khẩu.
- **Dashboard:** Thống kê tổng quan (tổng sản phẩm, tổng đơn hàng, đơn hôm nay, doanh thu, đơn pending, số đánh giá, điểm rating trung bình).
- **Quản lý cửa hàng:** Xem, chỉnh sửa thông tin shop, upload/xóa avatar và banner.
- **Quản lý sản phẩm:** CRUD sản phẩm đầy đủ kèm biến thể (màu, size, SKU, giá lẻ, giá sỉ, tồn kho) và ảnh sản phẩm.
- **Quản lý đơn hàng:** Xem danh sách, xem chi tiết, cập nhật trạng thái thanh toán, cập nhật trạng thái vận chuyển.
- **Quản lý đánh giá:** Xem đánh giá của khách, phản hồi đánh giá.
- **Đa ngôn ngữ:** Chuyển đổi giao diện giữa Tiếng Việt và Tiếng Trung Giản thể.

### Seller REST API (`/api/seller/*`)
Tương đương các tính năng Seller Dashboard nhưng trả về JSON (dùng cho tích hợp với ứng dụng seller riêng biệt).

---

## Luồng hoạt động

### Luồng mua hàng (Buyer)

```
1. Buyer đăng ký / đăng nhập  →  nhận JWT token
2. Duyệt sản phẩm (không cần auth)
3. Toggle like / track view  →  cần JWT (auth.token)
4. Thêm vào giỏ hàng  →  cần JWT
5. Tạo đơn hàng:
   a. Chọn địa chỉ giao hàng (shipping_address_id)
   b. POST /api/orders với danh sách biến thể, số lượng, cờ is_wholesale
   c. Server snapshot địa chỉ vào bản ghi order (tránh mất dữ liệu khi user sửa địa chỉ sau)
   d. Tạo Payment record (status: pending)
   e. Tạo Shipment record
   f. Xóa cart items tương ứng (nếu truyền cart_item_id)
6. Buyer theo dõi trạng thái đơn (pending → paid → shipping → completed / cancelled)
7. Buyer để lại đánh giá cho sản phẩm
```

### Luồng quản lý (Seller Web)

```
1. Seller đăng nhập tại /seller/login  →  session cookie
2. Xem dashboard: tổng quan doanh thu, đơn hàng hôm nay
3. Tạo/cập nhật sản phẩm với biến thể và hình ảnh
4. Khi có đơn mới: xem chi tiết, cập nhật trạng thái payment & shipment
5. Phản hồi đánh giá của khách hàng
6. Cập nhật thông tin shop (avatar, banner, mô tả)
```

### Xác thực JWT (tự triển khai)

```
1. Server ký token bằng HMAC-SHA256 sử dụng APP_KEY
2. Payload gồm: sub (user_id), role, exp (thời gian hết hạn)
3. Middleware AuthToken / AuthTokenSeller giải mã, kiểm tra chữ ký và exp
4. User được gắn vào $request->attributes->get('auth_user')
```

---

## Database Schema

### Bảng chính

| Bảng | Mô tả |
|---|---|
| `users` | Tài khoản hệ thống; role: `buyer`, `seller`, `admin`; login_provider: `local`, `google`, `facebook`, `apple` |
| `suppliers` | Hồ sơ cửa hàng của seller; 1-1 với users; có avatar, banner, rating_avg |
| `categories` | Danh mục sản phẩm |
| `products` | Sản phẩm; thuộc về 1 supplier và 1 category; status: `active`, `hidden` |
| `product_variants` | Biến thể sản phẩm (màu, size, SKU); lưu cả `price` (lẻ) và `wholesale_price` (sỉ) |
| `product_images` | Ảnh sản phẩm (nhiều ảnh/sản phẩm) |
| `product_views` | Lượt xem sản phẩm theo user (unique user-product) |
| `product_likes` | Lượt thích theo user (unique user-product) |
| `carts` | Giỏ hàng (1 user có 1 cart) |
| `cart_items` | Mục trong giỏ hàng |
| `orders` | Đơn hàng; status: `pending`, `paid`, `shipping`, `completed`, `cancelled`; lưu snapshot địa chỉ giao hàng |
| `order_items` | Chi tiết sản phẩm trong đơn hàng; có `is_wholesale` để phân biệt sỉ/lẻ |
| `payments` | Thông tin thanh toán; status: `pending`, `success`, `failed` |
| `shipments` | Thông tin vận chuyển; có `tracking_code` |
| `reviews` | Đánh giá sản phẩm (rating 1-5); có `seller_reply` và `replied_at` |
| `shipping_addresses` | Địa chỉ giao hàng của buyer; có `is_default` |
| `provinces` | Danh sách tỉnh/thành phố |
| `districts` | Danh sách quận/huyện theo tỉnh |

### Quan hệ chính

```
User (1) ──── (1) Supplier ──── (n) Products ──── (n) ProductVariants
                                                └── (n) ProductImages
User (1) ──── (1) Cart ──── (n) CartItems ───────────────────────────────┐
User (1) ──── (n) Orders ──── (n) OrderItems ── ProductVariant ──────────┘
                         └─── (1) Payment
                         └─── (1) Shipment
User (1) ──── (n) Reviews (per product)
User (1) ──── (n) ShippingAddresses
User (1) ──── (n) ProductViews
User (1) ──── (n) ProductLikes
Province (1) ── (n) Districts
```

---

## Cài đặt môi trường local

### Yêu cầu

- [Docker](https://docs.docker.com/get-docker/) >= 24.x
- [Docker Compose](https://docs.docker.com/compose/) >= 2.x

> Không cần cài PHP, Composer, hay Node.js trực tiếp trên máy host — tất cả chạy trong container.

### Các bước cài đặt

```bash
# 1. Clone repository
git clone <repository-url> KidsMarket
cd KidsMarket

# 2. Tạo file .env cho Laravel
cp src/.env.example src/.env

# 3. Build và khởi động containers
docker compose up -d --build

# 4. Cài PHP dependencies
docker exec laravel_app composer install

# 5. Tạo application key
docker exec laravel_app php artisan key:generate

# 6. Chạy migrations
docker exec laravel_app php artisan migrate

# 7. (Tùy chọn) Seed dữ liệu mẫu
docker exec laravel_app php artisan db:seed

# 8. Cài Node dependencies và build assets
docker exec laravel_app npm install
docker exec laravel_app npm run build
```

---

## Cấu hình biến môi trường

Tạo file `src/.env` từ `src/.env.example`. Các biến quan trọng cần cấu hình:

```dotenv
APP_NAME=KidsMarket
APP_ENV=local
APP_KEY=                        # Tự động điền sau khi chạy php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database — khớp với docker-compose.yml
DB_CONNECTION=mysql
DB_HOST=mysql                   # Tên service trong Docker network
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Queue
QUEUE_CONNECTION=database

# Mail (cần cấu hình nếu dùng tính năng reset mật khẩu)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# Filesystem
FILESYSTEM_DISK=public
```

> **Lưu ý:** `APP_KEY` được dùng làm secret để ký JWT token. Không bao giờ commit `APP_KEY` lên version control.

---

## Chạy project

### Sử dụng Docker (khuyến nghị)

```bash
# Khởi động tất cả services
docker compose up -d

# Dừng services
docker compose down

# Xem logs
docker compose logs -f app
```

| URL | Mô tả |
|---|---|
| `http://localhost:8000` | Laravel application |
| `http://localhost:8000/seller` | Seller Dashboard |
| `http://localhost:8000/api` | REST API base URL |
| `http://localhost:8080` | phpMyAdmin |

### Chạy local không dùng Docker

```bash
cd src

# Cài dependencies
composer install
npm install

# Tạo .env và key
cp .env.example .env
php artisan key:generate

# Cấu hình DB trong .env rồi chạy migration
php artisan migrate

# Khởi động (tất cả services cùng lúc)
composer dev
```

Lệnh `composer dev` sử dụng `concurrently` để chạy đồng thời: PHP built-in server, queue worker, Pail log viewer, và Vite dev server.

---

## Build & Deploy

### Build assets frontend

```bash
# Development (watch mode)
docker exec laravel_app npm run dev

# Production build
docker exec laravel_app npm run build
```

File output được tạo tại `src/public/build/`.

### Deploy Production

```bash
# Trên server production, sau khi pull code mới
docker exec laravel_app composer install --no-dev --optimize-autoloader
docker exec laravel_app php artisan migrate --force
docker exec laravel_app npm run build
docker exec laravel_app php artisan config:cache
docker exec laravel_app php artisan route:cache
docker exec laravel_app php artisan view:cache
```

> **Lưu ý:** Đặt `APP_ENV=production` và `APP_DEBUG=false` trong `.env` production. Thay đổi mật khẩu MySQL mặc định trong `docker-compose.yml` trước khi deploy.

---

## API Reference

### Base URL
```
http://localhost:8000/api
```

Tất cả response JSON theo cấu trúc:
```json
{
  "success": true | false,
  "message": "...",
  "data": { ... }
}
```

### Authentication

| Method | Endpoint | Auth | Mô tả |
|---|---|---|---|
| POST | `/register` | — | Đăng ký tài khoản buyer |
| POST | `/login` | — | Đăng nhập (username/email + password) |
| POST | `/social-login` | — | Đăng nhập mạng xã hội |
| GET | `/check-username` | — | Kiểm tra username đã tồn tại chưa |

**Header xác thực:**
```
Authorization: Bearer <jwt_token>
```

### Sản phẩm

| Method | Endpoint | Auth | Mô tả |
|---|---|---|---|
| GET | `/products` | — | Danh sách sản phẩm (filter: `category_id`, `supplier_id`, `search`, `per_page`) |
| GET | `/products/search?q=` | — | Autocomplete tìm kiếm |
| GET | `/products/recommendations/home` | Optional | Gợi ý trang chủ |
| GET | `/products/{id}` | Optional | Chi tiết sản phẩm |
| POST | `/products/{id}/track-view` | Required | Ghi nhận lượt xem |
| POST | `/products/{id}/like` | Required | Toggle like/unlike |

### Giỏ hàng

| Method | Endpoint | Auth | Mô tả |
|---|---|---|---|
| GET | `/cart` | Required | Xem giỏ hàng |
| POST | `/cart` | Required | Thêm/cập nhật item trong giỏ |

### Đơn hàng

| Method | Endpoint | Auth | Mô tả |
|---|---|---|---|
| GET | `/orders` | Required | Danh sách đơn hàng (filter: `status`, `from_date`, `to_date`, `keyword`) |
| POST | `/orders` | Required | Tạo đơn hàng mới |
| GET | `/orders/{id}` | Required | Chi tiết đơn hàng |
| PATCH | `/orders/{id}/cancel` | Required | Hủy đơn hàng |

**Body tạo đơn hàng:**
```json
{
  "shipping_address_id": 1,
  "items": [
    {
      "product_variant_id": 5,
      "quantity": 2,
      "is_wholesale": false,
      "cart_item_id": 12
    }
  ]
}
```

### Đánh giá

| Method | Endpoint | Auth | Mô tả |
|---|---|---|---|
| GET | `/products/{id}/reviews` | — | Danh sách đánh giá kèm thống kê |
| POST | `/products/{id}/reviews` | Required | Gửi đánh giá (rating 1-5) |

### Địa chỉ giao hàng

| Method | Endpoint | Auth | Mô tả |
|---|---|---|---|
| GET | `/shipping-addresses` | Required | Danh sách địa chỉ |
| POST | `/shipping-addresses` | Required | Tạo địa chỉ mới |
| PUT | `/shipping-addresses/{id}` | Required | Cập nhật địa chỉ |
| PATCH | `/shipping-addresses/{id}/set-default` | Required | Đặt làm địa chỉ mặc định |
| DELETE | `/shipping-addresses/{id}` | Required | Xóa địa chỉ |

### Địa lý

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/provinces` | Danh sách tỉnh/thành phố |
| GET | `/provinces/{id}` | Chi tiết tỉnh |
| GET | `/provinces/{id}/districts` | Danh sách quận/huyện theo tỉnh |

### Cửa hàng (public)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/shops/{id}` | Thông tin cửa hàng |
| GET | `/shops/{id}/products` | Sản phẩm của cửa hàng |

### Seller API (`/api/seller/*`)

Xác thực bằng JWT riêng của seller (seller `role` trong token):

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/seller/auth/register` | Đăng ký tài khoản seller |
| POST | `/seller/auth/login` | Đăng nhập |
| POST | `/seller/auth/logout` | Đăng xuất |
| GET | `/seller/dashboard` | Thống kê tổng quan |
| GET/PUT | `/seller/shop` | Xem/cập nhật thông tin shop |
| DELETE | `/seller/shop/avatar` | Xóa avatar |
| DELETE | `/seller/shop/banner` | Xóa banner |
| GET/POST | `/seller/products` | Danh sách / Tạo sản phẩm |
| GET/PUT/DELETE | `/seller/products/{id}` | Chi tiết / Cập nhật / Xóa |
| DELETE | `/seller/products/images/{imageId}` | Xóa ảnh sản phẩm |
| GET | `/seller/orders` | Danh sách đơn hàng |
| GET | `/seller/orders/{id}` | Chi tiết đơn hàng |
| GET/POST | `/seller/reviews` | Xem / Phản hồi đánh giá |

> File Postman collection đầy đủ: `KidsMarket_Seller_API.postman_collection.json` (tại thư mục gốc repository).

### Rate Limiting

| Endpoint | Giới hạn |
|---|---|
| POST `/api/login` | 10 request/phút/tài khoản+IP |
| POST `/api/register` | 15 request/giờ/IP |
| POST `/api/social-login` | 20 request/phút/email+IP |
| GET `/api/check-username` | 30 request/phút/IP |
| POST `/seller/login` | 5 request/phút/email+IP |
| POST `/seller/register` | 10 request/giờ/IP |
| POST `/seller/forgot-password` | 6 request/giờ/email+IP |

---

## Database Migration & Seed

```bash
# Chạy tất cả migrations
docker exec laravel_app php artisan migrate

# Rollback migration cuối
docker exec laravel_app php artisan migrate:rollback

# Reset và chạy lại toàn bộ
docker exec laravel_app php artisan migrate:fresh

# Seed toàn bộ dữ liệu mẫu
docker exec laravel_app php artisan db:seed

# Seed một seeder cụ thể
docker exec laravel_app php artisan db:seed --class=ProductSeeder

# Fresh migration + seed (môi trường dev)
docker exec laravel_app php artisan migrate:fresh --seed
```

**Các Seeder có sẵn:**

| Seeder | Mô tả |
|---|---|
| `UserSeeder` | Tạo user mẫu (buyer, seller) |
| `SupplierSeeder` | Tạo hồ sơ cửa hàng |
| `CategorySeeder` | Tạo danh mục sản phẩm |
| `ProductSeeder` | Tạo sản phẩm mẫu |
| `ProductVariantSeeder` | Tạo biến thể sản phẩm |
| `ProductImageSeeder` | Tạo ảnh sản phẩm |
| `ProvinceSeeder` | Import danh sách tỉnh/thành phố |
| `DistrictSeeder` | Import danh sách quận/huyện |
| `ShippingAddressSeeder` | Tạo địa chỉ giao hàng mẫu |
| `OrderSeeder` | Tạo đơn hàng mẫu |

---

## Ví dụ sử dụng API

### Đăng ký tài khoản buyer

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "fullname": "Nguyen Van A",
    "username": "nguyenvana",
    "password": "Password123!",
    "phone": "0901234567",
    "dob": "1995-01-15"
  }'
```

### Đăng nhập và lấy token

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "nguyenvana",
    "password": "Password123!"
  }'
# Response: { "success": true, "data": { "access_token": "eyJ..." } }
```

### Lấy danh sách sản phẩm

```bash
curl "http://localhost:8000/api/products?category_id=1&per_page=20"
```

### Tạo đơn hàng

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address_id": 1,
    "items": [
      { "product_variant_id": 3, "quantity": 2, "is_wholesale": false, "cart_item_id": 7 }
    ]
  }'
```

---

## Lưu ý phát triển & mở rộng

### Authentication

- JWT được ký thủ công bằng HMAC-SHA256 với `APP_KEY` — không phụ thuộc thư viện bên ngoài.
- Buyer và Seller sử dụng payload JWT riêng biệt và middleware khác nhau (`auth.token` vs `auth.token.seller`).
- `auth.token.optional` dùng cho các endpoint không bắt buộc đăng nhập nhưng cần personalization (vd: gợi ý sản phẩm, đánh dấu `is_liked`).

### Giá sỉ / lẻ

- Mỗi `product_variant` có cả `price` (lẻ) và `wholesale_price` (sỉ).
- Khi tạo đơn, buyer truyền `is_wholesale: true/false` cho từng item.
- Bản ghi `order_items` lưu cờ `is_wholesale` để tính lại giá đúng.

### Đa ngôn ngữ Seller UI

- Ngôn ngữ hiện tại được lưu trong `session('seller_locale')` (mặc định `vi`).
- Chuyển ngôn ngữ bằng cách `POST /seller/language` với body `{"locale": "zh"}`.
- Tất cả chuỗi trong Blade dùng `__('seller.key')` hoặc `@lang('seller.key')` trong thẻ `<script>`.
- Thêm ngôn ngữ mới: tạo file `lang/{locale}/seller.php` và thêm vào whitelist trong `SetSellerLocale.php`.

### Snapshot địa chỉ giao hàng

- Khi tạo đơn hàng, thông tin địa chỉ giao hàng được copy vào bảng `orders` (`shipping_full_name`, `shipping_phone`, `shipping_address_detail`, `shipping_province_id`, `shipping_district_id`).
- Điều này đảm bảo dữ liệu đơn hàng không bị thay đổi dù user cập nhật địa chỉ sau khi đặt.

### Thêm tính năng mới

- **Thêm API endpoint mới:** Tạo method trong controller tương ứng trong `app/Http/Controllers/Api/`, đăng ký route trong `routes/api.php`.
- **Thêm Seller web page:** Tạo method trong `app/Http/Controllers/Seller/`, tạo Blade template trong `resources/views/seller/`, đăng ký route trong `routes/seller.php`. Nhớ thêm chuỗi i18n vào cả `lang/vi/seller.php` và `lang/zh/seller.php`.
- **Thêm migration:** `docker exec laravel_app php artisan make:migration <name>`.
- **Rate limiting:** Thêm `RateLimiter::for(...)` trong `AppServiceProvider::boot()` và áp dụng middleware `throttle:<name>` lên route.

### Lưu ý bảo mật khi deploy

- Đổi `MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD` trong `docker-compose.yml` trước khi deploy.
- Đặt `APP_DEBUG=false` trong môi trường production.
- `APP_KEY` là bí mật ký JWT — không commit lên Git, không chia sẻ.
- Storage uploads: chạy `php artisan storage:link` để public symlink hoạt động đúng.

### Thông tin còn thiếu trong codebase hiện tại

> Các mục sau chưa có trong code hiện tại, cần xem xét khi mở rộng:
> - Chưa có file `.env.example` — cần tạo để developer mới biết các biến cần cấu hình.
> - Chưa có tích hợp payment gateway thực tế (VNPay, Momo, v.v.) — bảng `payments` hiện chỉ lưu metadata.
> - Chưa có tích hợp shipping provider thực tế — bảng `shipments` chỉ có `tracking_code` và `status`.
> - Chưa có admin panel — chỉ có `buyer` và `seller` được triển khai, `admin` role tồn tại trong DB nhưng chưa có controller/view.
> - Chưa có refresh token — JWT hiện tại là stateless one-time token, hết hạn là đăng nhập lại.
> - Chưa có event/notification hệ thống — không có email thông báo đặt hàng thành công, v.v. (ngoại trừ reset mật khẩu seller).

