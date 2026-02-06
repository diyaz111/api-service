# API User Service

A REST API built with Laravel for managing users, products, and orders. It includes authentication via Laravel Sanctum, role-based access, and a consistent JSON response format.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Project Structure](#project-structure)
- [API Overview](#api-overview)
- [License](#license)

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Laravel 12.x |
| **PHP** | ^8.2 |
| **Authentication** | Laravel Sanctum (API tokens) |
| **Database** | SQLite (default), supports MySQL/PostgreSQL via config |
| **Mail** | Laravel Mail (log driver by default; configurable to SMTP) |

**Notable features:**

- Form Request validation for all inputs
- Centralized API response format (`ApiResponse`)
- Custom exception handling for API (validation, 401, 404)
- Mailable classes for user-creation and admin-notification emails

---

## Requirements

- **PHP** >= 8.2 (with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML)
- **Composer**
- **Node.js & npm** (optional; for front-end assets if you use them)

---

## Installation

### 1. Clone the repository

```bash
git clone <repository-url> api-user-service
cd api-user-service
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database setup

The default `.env` uses SQLite. Ensure the database file exists and run migrations:

```bash
# Using SQLite (default): create the database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

For **MySQL** or **PostgreSQL**, update `.env` with your DB credentials and run `php artisan migrate` (no need to create a file).

### 5. (Optional) Install front-end dependencies

Only needed if you work with Laravel’s default front-end build:

```bash
npm install
npm run build
```

---

## Configuration

### Environment variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_KEY` | Application key (set by `php artisan key:generate`) | — |
| `DB_CONNECTION` | Database driver | `sqlite` |
| `MAIL_MAILER` | Mail driver | `log` |

- **Mail:** By default, mail is logged to `storage/logs/laravel.log`. Set `MAIL_MAILER`, `MAIL_HOST`, etc. in `.env` to send real emails.

---

## Running the Application

Start the development server:

```bash
php artisan serve
```

API base URL: **http://localhost:8000** (or the host/port shown in the terminal).

Optional: run queue worker if you queue mail/jobs:

```bash
php artisan queue:work
```

---

## Project Structure

```
api-user-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/                    # API controllers
│   │   │       ├── AuthController.php  # Login, token issuance
│   │   │       ├── UserController.php  # Create user, list users
│   │   │       ├── ProductController.php# Create/list products
│   │   │       └── OrderController.php # Create orders
│   │   ├── Requests/                   # Form Request validation
│   │   │   ├── StoreUserRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── StoreOrderRequest.php
│   │   │   ├── IndexUsersRequest.php
│   │   │   └── LoginRequest.php
│   │   └── Responses/
│   │       └── ApiResponse.php         # Unified JSON response helper
│   ├── Mail/
│   │   ├── AccountCreatedMail.php      # Email to new user
│   │   └── NewUserNotificationMail.php # Email to admin
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   └── Order.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                         # Exception handling for API
├── config/                             # App, auth, database, mail, sanctum, etc.
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       └── emails/                     # Blade templates for mail
├── routes/
│   ├── api.php                         # All API routes (prefix: /api)
│   ├── web.php
│   └── console.php
├── storage/logs/
├── .env.example
├── composer.json
└── README.md
```

### Code flow (high level)

- **Routes** (`routes/api.php`) define HTTP method and URI, and optional `auth:sanctum` middleware.
- **Form Requests** validate input; invalid requests return a structured validation error response.
- **Controllers** use `ApiResponse::success()` / `ApiResponse::error()` so all responses share the same JSON shape.
- **Exception handling** in `bootstrap/app.php` converts validation, authentication, and HTTP exceptions into the same API response format for `api/*` and JSON requests.

---

## API Overview

Base path: **`/api`**. Send **`Accept: application/json`** (and **`Content-Type: application/json`** for POST/PUT) for JSON responses.

### Response format

**Success:**

```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

**Error (e.g. validation, 401, 404):**

```json
{
  "success": false,
  "message": "...",
  "errors": { ... }
}
```

### Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/login` | No | Login; returns Bearer token and user info |
| POST | `/api/users` | No | Create user; sends confirmation + admin emails |
| GET | `/api/users` | Bearer token | List users (paginated, search, sort); includes `orders_count`, `can_edit` |
| POST | `/api/products` | Bearer token | Create product |
| GET | `/api/products` | Bearer token | List all products |
| POST | `/api/orders` | Bearer token | Create order (`product_id` optional) |

### Authentication

- **Login:** `POST /api/login` with `email` and `password`; response includes `token`.
- **Protected routes:** Send header **`Authorization: Bearer <token>`**.
- **User roles:** `administrator`, `manager`, `user`. Used for `can_edit` on GET `/api/users` (admin: all; manager: only `user` role; user: only self).

### Example: create user

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"john@example.com","password":"password123","name":"John Doe"}'
```

### Example: get users (with token)

```bash
curl -X GET "http://localhost:8000/api/users?page=1&sortBy=name" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
