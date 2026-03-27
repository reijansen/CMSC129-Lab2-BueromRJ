# Finko

Finko is a Laravel-based student budget and finance tracker. It helps users manage categories, budgets, and transactions, including search/filter, soft-delete trash management, relationship views, and transaction attachment uploads.

## Features

- User authentication (register, login, forgot password)
- Dashboard overview (allocated budget, expenses, income, balance, active budgets)
- Category management (create, edit, view, delete)
- Budget management (create, edit, view, soft delete, restore, force delete)
- Transaction management (create, edit, view, soft delete, restore, force delete)
- Relationship-aware pages (category-to-budgets/transactions, budget-to-transactions)
- Search and filter for budgets and transactions with pagination-safe query strings
- Transaction attachment upload, replacement, preview/link, and permanent-delete file cleanup
- User-scoped data protection across all modules
- Database seeding with realistic Faker data for demo/testing

## Tech Stack (and How It Was Used)

- **Laravel (PHP Framework)**  
  Used as the core backend framework for MVC structure, routing, controllers, models, validation, middleware, and seeding.

- **Blade Templating**  
  Used to build all UI pages (guest pages, dashboard, CRUD pages, trash pages) with reusable layouts and partials.

- **Tailwind CSS**  
  Used for consistent styling across cards, forms, tables, badges, buttons, empty states, and alert components.

- **PostgreSQL (Supabase)**  
  Used as the relational database for users, categories, budgets, and transactions, including foreign keys and soft-delete support.

- **Eloquent ORM**  
  Used to manage model relationships (`belongsTo`, `hasMany`), user-scoped querying, eager loading, and query filtering.

- **Laravel Storage**  
  Used for transaction attachment upload, replacement, and cleanup on permanent delete (`storage/app/public` + `storage:link`).

- **Laravel Factories + Seeders + Faker**  
  Used to generate realistic demo/test data with relationship-safe ownership across users, categories, budgets, and transactions.

- **Vite + npm**  
  Used for asset bundling and running frontend build/dev workflow for Blade + Tailwind resources.

## Installation / Setup

1. Clone the repository.
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install frontend dependencies:
   ```bash
   npm install
   ```
4. Create your environment file:
   ```bash
   copy .env.example .env
   ```
5. Generate app key:
   ```bash
   php artisan key:generate
   ```
6. Configure database credentials in `.env` (Supabase PostgreSQL connection values).
7. Run migrations and seed sample data:
   ```bash
   php artisan migrate:fresh --seed
   ```
8. Create storage symlink (needed for uploaded transaction attachments):
   ```bash
   php artisan storage:link
   ```
9. Build/run assets and start app:
   ```bash
   npm run dev
   php artisan serve
   ```

## Usage Instructions

1. Open the app in your browser (default: `http://127.0.0.1:8000`).
2. Login using seeded demo credentials:
   - `demo1@finko.test` / `password123`
   - `demo2@finko.test` / `password123`
3. Use sidebar navigation to access:
   - Dashboard
   - Categories
   - Budgets
   - Transactions
4. Typical workflow:
   - Create categories
   - Create budgets linked to categories
   - Add transactions linked to both budgets and categories
   - Use filters/search on budgets and transactions pages
   - View and manage transaction attachments

## Documentation Linkage

- [Environment Example](.env.example)
- [Web Routes](routes/web.php)
- [Database Seeder](database/seeders/DatabaseSeeder.php)
- [Budget Controller](app/Http/Controllers/BudgetController.php)
- [Transaction Controller](app/Http/Controllers/TransactionController.php)
- [Category Controller](app/Http/Controllers/CategoryController.php)
- [Laravel Markdown Guide](https://docs.github.com/en/get-started/writing-on-github)

## Author / Contributors

- Primary Developer: `BueromRJ`
- Course Project: CMSC 129 (Software Engineering II)
