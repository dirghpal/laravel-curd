# 🚀 Laravel 12 REST API

A production-ready RESTful API built with **Laravel 12** following REST API best practices. This project includes authentication, role-based authorization, CRUD operations, Swagger documentation, API Resources, Form Requests, and automated feature tests.

---

## ✨ Features

- 🔐 Laravel Sanctum Authentication
- 👤 User Registration & Login
- 🚪 Logout
- ✉️ Email Verification
- 🔑 Forgot & Reset Password
- 👮 Role-Based Authorization (Admin/User)
- 📦 Product CRUD
- 🗂️ Category CRUD
- 📝 Post CRUD
- 💬 Comment System
- ❤️ Like System
- 🖼️ Product Image Upload
- 🔍 Search
- 🎯 Filtering
- ↕️ Sorting
- 📄 Pagination
- 📚 API Resources
- ✅ Form Request Validation
- 📖 Swagger (OpenAPI) Documentation
- 🧪 Feature Testing (PHPUnit)
- 🌱 Database Seeders
- 🔢 API Versioning (`/api/v1`)

---

# 🛠️ Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL
- Laravel Sanctum
- Swagger (L5-Swagger)
- PHPUnit
- Composer

---

# 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/

database/
├── migrations/
├── factories/
└── seeders/

routes/
tests/
storage/
```

---

# ⚙️ Installation

Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/laravel-crud.git
```

Go to project folder

```bash
cd laravel-crud
```

Install dependencies

```bash
composer install
```

Copy environment file

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

Configure your database in `.env`

Run migrations and seeders

```bash
php artisan migrate --seed
```

Create storage link

```bash
php artisan storage:link
```

Start the server

```bash
php artisan serve
```

---

# 📖 Swagger Documentation

Generate Swagger documentation

```bash
php artisan l5-swagger:generate
```

Open in browser

```
http://127.0.0.1:8000/api/documentation
```

---

# 🧪 Run Tests

```bash
php artisan test
```

---

# 🔐 Authentication

This project uses **Laravel Sanctum**.

Use the Bearer Token returned after login.

Example:

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

# 📦 Main API Endpoints

## Authentication

| Method | Endpoint |
|--------|----------|
| POST | /api/v1/register |
| POST | /api/v1/login |
| POST | /api/v1/logout |

---

## Products

| Method | Endpoint |
|--------|----------|
| GET | /api/v1/products |
| POST | /api/v1/products |
| GET | /api/v1/products/{id} |
| PUT | /api/v1/products/{id} |
| DELETE | /api/v1/products/{id} |

---

## Categories

| Method | Endpoint |
|--------|----------|
| GET | /api/v1/categories |
| POST | /api/v1/categories |
| PUT | /api/v1/categories/{id} |
| DELETE | /api/v1/categories/{id} |

---

## Posts

| Method | Endpoint |
|--------|----------|
| GET | /api/v1/posts |
| POST | /api/v1/posts |
| PUT | /api/v1/posts/{id} |
| DELETE | /api/v1/posts/{id} |

---

## Comments

| Method | Endpoint |
|--------|----------|
| POST | /api/v1/posts/{id}/comments |
| DELETE | /api/v1/comments/{id} |

---

## Likes

| Method | Endpoint |
|--------|----------|
| POST | /api/v1/posts/{id}/like |
| DELETE | /api/v1/posts/{id}/like |

---

# 📷 Screenshots

## Swagger UI

> Add screenshot here

```
screenshots/swagger.png
```

## Products API

> Add screenshot here

```
screenshots/products.png
```

---

# 📂 Postman Collection

Import the Postman collection from:

```
postman/Laravel_API.postman_collection.json
```

---

# 📌 API Version

Current API Version

```
v1
```

---

# 👨‍💻 Author

**Dirghpal Suthar**

- GitHub: https://github.com/YOUR_USERNAME
- LinkedIn: https://linkedin.com/in/YOUR_LINKEDIN

---

# ⭐ If you like this project

Please consider giving this repository a ⭐ on GitHub.
