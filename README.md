# SIKPK App

A modern web application built using:

- Laravel 13
- Vue 3
- Inertia.js
- TypeScript
- TailwindCSS
- Shadcn Vue

---

# ⚙️ Version & Requirements

| Component  | Minimum Version |
| ---------- | --------------- |
| PHP        | >= 8.3          |
| Node.js    | >= 18.x         |
| PostgreSQL | >= 15           |
| Composer   | >= 2.x          |
| Docker     | Latest          |

---

# 🚀 Tech Stack

## Backend

- Laravel 13
- PostgreSQL
- Service Pattern

## Frontend

- Vue 3
- TypeScript
- Inertia.js
- TailwindCSS
- Vue Sonner
- Shadcn Vue

---

# 📁 Project Structure

```bash
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
└── Traits/

resources/
├── js/
│   ├── components/
│   ├── layouts/
│   ├── pages/
│   ├── types/
│   └── lib/
└── views/
```

---

# ✅ Naming Convention

## 🎮 Controller

| Element      | Convention              | Example              |
| ------------ | ----------------------- | -------------------- |
| File & Class | PascalCase + Controller | `UserController.php` |
| Method       | camelCase               | `index()`, `store()` |

---

## 🧠 Model

| Element      | Convention | Example    |
| ------------ | ---------- | ---------- |
| File & Class | PascalCase | `User.php` |

---

## ⚙️ Service

| Element      | Convention           | Example           |
| ------------ | -------------------- | ----------------- |
| File & Class | PascalCase + Service | `UserService.php` |

---

## 🌐 Route

| Element    | Convention   | Example              |
| ---------- | ------------ | -------------------- |
| URI        | kebab-case   | `/master/users`      |
| Route Name | dot.notation | `master.users.index` |

---

## 🗂 Vue Pages

| Element | Convention | Example                 |
| ------- | ---------- | ----------------------- |
| Folder  | kebab-case | `master/`, `dashboard/` |
| File    | PascalCase | `Index.vue`             |

---

## 🧩 Vue Components

| Element    | Convention | Example            |
| ---------- | ---------- | ------------------ |
| File       | PascalCase | `DataTable.vue`    |
| Composable | camelCase  | `useAppearance.ts` |

---

## 🛠 Database

### Table

| Convention         | Example                    |
| ------------------ | -------------------------- |
| snake_case, plural | `users`, `post_categories` |

### Column

| Convention | Example                |
| ---------- | ---------------------- |
| snake_case | `must_change_password` |

---

# 📝 Git Commit Convention

| Type     | Description                              |
| -------- | ---------------------------------------- |
| feat     | Add new feature                          |
| fix      | Bug fix                                  |
| refactor | Code improvement without feature changes |
| mod      | Modify existing feature                  |
| docs     | Documentation changes                    |
| style    | Styling/UI changes                       |
| chore    | Maintenance/configuration                |

---

# 📦 Local Development Installation

## 1. Clone Repository

```bash
git clone <repository-url>
cd sikpk-app
```

---

## 2. Install Backend Dependencies

```bash
composer install
```

---

## 3. Install Frontend Dependencies

Using pnpm:

```bash
pnpm install
```

or npm:

```bash
npm install
```

---

## 4. Copy Environment File

```bash
cp .env.example .env
```

---

## 5. Configure Environment

Example PostgreSQL configuration:

```env
APP_NAME=SIKPK
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sikpk_db
DB_USERNAME=postgres
DB_PASSWORD=secure_password
```

---

## 6. Generate Application Key

```bash
php artisan key:generate
```

---

## 7. Run Migration

```bash
php artisan migrate
```

---

## 8. Run Seeder (Optional)

```bash
php artisan db:seed
```

---

## 9. Run Development Server

### Backend

```bash
php artisan serve
```

### Frontend

Using pnpm:

```bash
pnpm dev
```

or npm:

```bash
npm run dev
```

---

# 🐳 Running with Docker

---

## 1. Copy Environment File

```bash
cp .env.example .env
```

---

## 2. Configure Docker Database Environment

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=sikpk_db
DB_USERNAME=postgres
DB_PASSWORD=secure_password
```

---

## 3. Build Docker Container

```bash
docker compose build
```

---

## 4. Run Docker Container

```bash
docker compose up -d
```

---

## 5. Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

---

## 6. Run Migration

```bash
docker compose exec app php artisan migrate
```

---

## 7. Access Application

| Service         | URL                   |
| --------------- | --------------------- |
| Application     | http://localhost      |
| Vite Dev Server | http://localhost:5173 |
| Adminer         | http://localhost:9090 |

---

# 🧪 Useful Commands

## Clear Cache

```bash
php artisan optimize:clear
```

---

## Run Queue Worker

```bash
php artisan queue:work
```

---

## Run Test

```bash
php artisan test
```

---

# 🔐 Authentication Flow

```text
Admin creates user
↓
System generates random password
↓
Credentials sent via email
↓
User logs in
↓
User forced to change password
↓
Account activated
```

---

# 🧱 Architecture Pattern

## Backend

- Service Pattern
- Thin Controller
- Business Logic in Services

## Frontend

- Reusable Components
- Generic DataTable
- Form Schema Driven
- Inertia CRUD Pattern

---

# 📄 License

This project is proprietary and intended for internal usage.
