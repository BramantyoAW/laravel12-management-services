# Laravel 12 GraphQL API with Lighthouse & JWT Auth

This is a backend service built with Laravel 12, Lighthouse (GraphQL server for Laravel), and Tymon JWT Auth, designed to support an admin dashboard that manages multiple applications (e.g. portfolio, linktree, etc.).

---

## 🏗️ Features

- 🔐 User authentication using **JWT**
- 📊 GraphQL API using **Lighthouse**
- 📁 User model with roles and secure password hashing
- 📥 Login mutation that returns JWT token and user info
- 🧑 Query to get authenticated user (`me`)
- 🔍 Pagination & filtering for user listing
- 🧾 Login logs stored in database and (optionally) in Firestore

---

## 🧰 Stack

| Component        | Technology          |
|------------------|---------------------|
| Framework        | Laravel 12          |
| API Style        | GraphQL (Lighthouse)|
| Auth             | JWT (`tymon/jwt-auth`) |
| DB               | MySQL / PostgreSQL  |
| Log Sync (async) | Firebase Firestore (optional) |

---

## ⚙️ Installation

1. **Clone the repo**  
   ```bash
   git clone https://github.com/your-username/laravel12-management.git
   cd laravel12-management


2. **Install dependencies**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   php artisan migrate
   php artisan db:seed
   php artisan serve

3. **Test the API**
   ```bash
   curl -X POST http://localhost:8000/graphql -H "Content-Type: application/json" -H "Authorization: Bearer <YOUR_JWT_TOKEN>" -d '{"query":"query { me { id username email } }"}'
   