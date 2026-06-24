# Finko

Finko is a Laravel-based student budget and finance tracker. It helps users manage categories, budgets, and transactions, including search/filter, soft-delete trash management, relationship views, and transaction attachment uploads.

## CMSC 129 Lab 3: AI Integration (Chatbot + Assistant)

This project includes **two** AI modes integrated into the main app UI:

- **Inquiry Chatbot (Read-only):** Answers questions about your existing data (categories, budgets, transactions) without performing changes.
- **CRUD Assistant (Action-capable):** Interprets natural-language commands to create/update/delete records. **Update/Delete always require confirmation** before executing.

### Where to Find It (UI)

- The chat widget is embedded on the main authenticated layout as a floating button (bottom-right).
- It’s available while you are viewing your dashboard and CRUD pages (no separate chat page).

### Conversation Context

- The app keeps recent conversation history in the session (last ~10 messages) and supports follow-up questions.

### API Security (Best Practice)

- AI calls are made **server-side only** (backend proxy pattern).
- `.env` is ignored by Git (see `.gitignore`) and **API/service configs are not exposed in frontend code**.

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
   - Recommended for local dev: use the **direct** Supabase Postgres host `db.<project_ref>.supabase.co` on port `5432` with user `postgres`.
   - If you use the **pooler** host, make sure your port/username match what Supabase shows (pooler commonly uses port `6543` and username like `postgres.<project_ref>`).
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

## AI Setup (Gemini API)

This project is prepared for CMSC 129 Lab 3 AI integration using a backend-only Gemini connection (no direct AI calls from the frontend).

1. Create a Google AI Studio API key.
2. Configure `.env` (do not commit it):
   - `AI_PROVIDER=gemini`
   - `GEMINI_API_KEY=...`
   - `GEMINI_MODEL=gemini-1.5-flash`
   - `GEMINI_FALLBACK_MODELS=gemini-3.1-flash-lite,gemini-2.5-flash-lite` (optional; no brackets, no spaces unless quoted)
   - `AI_REQUEST_TIMEOUT_SECONDS=30`

Optional local fallback (Ollama):
- Set `AI_PROVIDER=ollama` and configure `OLLAMA_BASE_URL`, `OLLAMA_MODEL`, `OLLAMA_ROUTER_MODEL`.

### Required Environment Variables

See `.env.example` for the complete list. Key ones for Lab 3:

- AI:
  - `AI_PROVIDER`
  - `GEMINI_API_KEY`
  - `GEMINI_MODEL`
  - `GEMINI_ROUTER_MODEL`
  - `OLLAMA_BASE_URL` (optional)
  - `OLLAMA_MODEL` (optional)
  - `OLLAMA_ROUTER_MODEL` (optional)
  - `AI_REQUEST_TIMEOUT_SECONDS`
- Database (Supabase/Postgres):
  - `DB_CONNECTION=pgsql`
  - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE`
  - `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY`

## How the AI Works (High-level)

- **Frontend:** `resources/js/ai-chat-widget.js` renders the widget and calls backend endpoints.
- **Backend endpoints (protected):**
  - `POST /api/ai/chat` (inquiry chatbot)
  - `POST /api/ai/chat/reset` (clear chat history)
  - `POST /api/ai/assistant` (CRUD assistant)
  - `POST /api/ai/assistant/confirm` (execute pending update/delete)
  - `POST /api/ai/assistant/cancel` (discard pending update/delete)

## Example Queries (Minimum Requirement: 5+)

Try these in **Chatbot** mode (read-only):

- “What categories do I have?”
- “How many categories are there (expense vs income)?”
- “List my latest 10 transactions.”
- “How much did I spend this week?”
- “What’s my biggest expense this month?”
- “Show my top spending categories for the last 30 days.”
- “What budgets do I have?”

Try these in **Assistant** mode (CRUD-capable):

- “Create a category named ‘Coffee’ as expense with color #a16207.”
- “Create a budget titled ‘April Food’ allocated 2500 for Food from 2026-04-01 to 2026-04-30.”
- “Add an expense transaction titled ‘Lunch’ amount 120 under category Food today.”
- “Update the budget ‘April Food’ allocated amount to 3000.” (requires confirm)
- “Delete the transaction ‘Lunch’.” (requires confirm)

## Screenshots (Placeholders)

Add your screenshots here before submission:

- Inquiry chatbot answering questions:
  - `![Inquiry Chatbot Demo](images/screenshots/chatbot-demo.png)`
- Assistant proposing an update/delete and asking for confirmation:
  - `![Assistant Propose + Confirm](images/screenshots/assistant-confirm.png)`
- After confirmation (data updated/deleted and reflected on the main page):
  - `![After Confirm Result](images/screenshots/after-confirm.png)`

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
- [API Routes (AI)](routes/api.php)
- [Database Seeder](database/seeders/DatabaseSeeder.php)
- [Budget Controller](app/Http/Controllers/BudgetController.php)
- [Transaction Controller](app/Http/Controllers/TransactionController.php)
- [Category Controller](app/Http/Controllers/CategoryController.php)
- [Laravel Markdown Guide](https://docs.github.com/en/get-started/writing-on-github)

## Author / Contributor

- Developer: `Rei Jansen Bueroom`
- Course Project: CMSC 129 (Software Engineering II)
