# Finko - CMSC 129 Lab 3 AI Integration Guide

**Complete AI Chatbot & Assistant Implementation with Professional UI/UX**

---

## 🎯 Overview

This is a **complete implementation** of CMSC 129 Lab 3 (AI Integration) built on top of the Lab 2 budget tracker application. The project includes:

- ✅ **Inquiry Chatbot** - Read-only assistant for answering questions about budgets, transactions, and categories
- ✅ **CRUD Assistant** - Action-capable AI that can create, update, and delete records with confirmation
- ✅ **Professional Chat Widget** - Floating UI with animations, typing indicators, timestamps, and suggestions
- ✅ **Security Best Practices** - Backend-only API calls, no direct database access from AI
- ✅ **Conversation Context** - Last 10 messages maintained for intelligent follow-ups
- ✅ **Dual-Confirmation Pattern** - All destructive operations require explicit user consent

---

## 🤖 AI Features

### Mode 1: Inquiry Chatbot (📖 Read-only)

**Purpose:** Answer questions about your financial data without making any changes.

**Capabilities:**
- List categories, budgets, transactions
- Calculate totals (income, expenses, savings)
- Identify spending trends and patterns
- Generate financial insights
- Maintain conversation context for follow-up questions

**Example Queries:**
```
"What categories do I have?"
"Show my latest 10 transactions"
"How much did I spend this week?"
"What's my biggest expense this month?"
"Which category has the most spending?"
"How many active budgets do I have?"
"What's my total income this month?"
```

### Mode 2: CRUD Assistant (⚙️ Action-capable)

**Purpose:** Execute database operations through natural language with safety confirmations.

**Capabilities:**
- **Create:** Add new categories, budgets, transactions
- **Read:** Retrieve and display data (same as chatbot)
- **Update:** Modify existing records with confirmation
- **Delete:** Remove records with explicit consent

**Example Queries:**
```
"Create a category named 'Transport' as expense"
"Create a budget titled 'April Food' allocated 5000 for Food"
"Add a transaction 'Lunch' amount 120 under Food today"
"Update budget amount to 6000" → [CONFIRM/CANCEL]
"Delete the transaction 'Lunch'" → [CONFIRM/CANCEL]
```

---

## ✨ UI/UX Features (Professional Standards)

### Visual Design
- **Gradient Toggle Button** - Emerald gradient with smooth hover animation and scale effect
- **Animated Panel** - Slides in from bottom with fade effect, smooth transitions
- **Message Bubbles** - Rounded corners with shadows, color-coded (user=emerald, assistant=slate)
- **Timestamps** - Each message shows send time in HH:MM format
- **Typing Indicator** - Animated dots that bounce while AI is processing
- **Empty State** - Helpful welcome message with suggested queries
- **Loading States** - Clear visual feedback during AI processing

### Interactions
- **Suggestion Pills** - Pre-filled query examples user can click
- **Keyboard Support** - Press Escape to close, Tab to navigate
- **Focus Management** - Input auto-focuses when panel opens
- **Real-time Scrolling** - Auto-scrolls to latest message
- **Smooth Animations** - Fade-in, slide-in effects on messages
- **Error Display** - Beautiful error alerts with icons

### Responsive Design
- **Mobile:** Full-width panel (92vw max)
- **Tablet:** Medium panel size
- **Desktop:** 420px fixed width
- **Touch-friendly:** Larger button targets
- **Portrait/Landscape:** Adapts to orientation

---

## 🔐 Architecture & Security

### How It Works (Backend-Only)

```
┌─────────────────┐
│  Web Browser    │
│  (Frontend)     │
└────────┬────────┘
         │
         │ HTTP POST /api/ai/chat
         │ or /api/ai/assistant
         │
┌────────▼────────────────────┐
│  Laravel Backend            │
│  ├─ AIChatController.php    │
│  ├─ AIAssistantController   │
│  └─ AIService.php           │
└────────┬────────────────────┘
         │
         │ Queries using Eloquent
         │
┌────────▼────────────┐
│  PostgreSQL DB      │
│  (Supabase)         │
└─────────────────────┘
         │
         │ Model classes extract data
         │
┌────────▼──────────────────────┐
│  Ollama LLM Service           │
│  (runs locally on port 11434) │
└───────────────────────────────┘
```

### Security Features
- ✅ **No Direct Database Access** - AI only calls backend APIs
- ✅ **Backend Proxy Pattern** - All sensitive operations server-side
- ✅ **Environment Variables** - API keys in `.env` (not in code)
- ✅ **CSRF Protection** - All requests include CSRF tokens
- ✅ **User-Scoped Queries** - AI only sees current user's data
- ✅ **Session-Based History** - Not persisted in database
- ✅ **Input Validation** - All user input validated on backend

---

## 📦 Installation & Setup

### Prerequisites
- PHP 8.1+
- Node.js 16+
- Composer
- **Ollama** (https://ollama.com) for local AI
- PostgreSQL (Supabase) database

### Step 1: Clone & Install Dependencies
```bash
git clone <repository-url>
cd finko

composer install
npm install
```

### Step 2: Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your credentials:

**Database (Supabase):**
```env
DB_CONNECTION=pgsql
DB_HOST=db.<project-ref>.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<your-password>
DB_SSLMODE=require
```

**AI (Gemini API):**
```env
AI_PROVIDER=gemini
GEMINI_API_KEY=<your-gemini-api-key>
GEMINI_MODEL=gemini-1.5-flash
GEMINI_ROUTER_MODEL=gemini-1.5-flash
GEMINI_FALLBACK_MODELS=gemini-3.1-flash-lite,gemini-2.5-flash-lite  # no brackets/spaces unless quoted
AI_REQUEST_TIMEOUT_SECONDS=30
```

### Step 3: Setup Gemini API
```bash
# No local service needed.
# Create an API key in Google AI Studio, then set GEMINI_API_KEY in your .env
```

### Step 4: Database & Assets
```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev    # In one terminal
php artisan serve   # In another terminal
```

### Step 5: Access the App
```
http://127.0.0.1:8000
```

**Demo Credentials:**
- Email: `demo1@finko.test`
- Password: `password123`

---

## 🚀 Using the Chat Widget

### Opening the Chat
1. Click the **floating green button** in the bottom-right corner
2. Chat panel slides up with fade animation
3. Input field automatically focuses
4. Suggested queries appear on first use

### Switching Modes
- **📖 Chatbot** - Click the book icon for read-only mode
- **⚙️ Assistant** - Click the gear icon for CRUD mode
- Selection persists in browser localStorage

### Asking Questions
1. **Type your query** in the input field
2. **Press Enter** or click the send button (paper plane icon)
3. **Wait for response** (typing indicator shows AI is thinking)
4. **View result** - Message appears with timestamp

### Confirmation Flow (Assistant Mode)
1. **Type command** like "Delete the transaction 'Lunch'"
2. **AI proposes action** with confirmation request
3. **Click Confirm** to execute or **Cancel** to discard
4. **Page auto-refreshes** after successful operation

### Resetting Chat
- Click the **trash icon** in the header
- History clears, suggestion pills reappear
- Continue with new conversation

### Closing Chat
- Click the **X button** in the header
- Or press **Escape** key
- Chat panel slides down and hides

---

## 📝 API Endpoints

All endpoints require authentication and POST method.

### Chat Endpoints

**Send message to chatbot:**
```
POST /api/ai/chat
Content-Type: application/json
X-CSRF-TOKEN: <token>

{"message": "What categories do I have?"}

Response:
{
  "reply": "You have 5 categories...",
  "history": [...]
}
```

**Reset chat history:**
```
POST /api/ai/chat/reset
Content-Type: application/json
X-CSRF-TOKEN: <token>

Response:
{
  "reply": "Chat history cleared.",
  "history": []
}
```

### Assistant Endpoints

**Send command to assistant:**
```
POST /api/ai/assistant
Content-Type: application/json
X-CSRF-TOKEN: <token>

{"message": "Create a category 'Coffee'"}

Response:
{
  "reply": "I'll create a category named 'Coffee'... Confirm?",
  "history": [...],
  "mode": "propose"
}
```

**Confirm pending action:**
```
POST /api/ai/assistant/confirm
Content-Type: application/json
X-CSRF-TOKEN: <token>

Response:
{
  "reply": "Category created successfully!",
  "history": [...],
  "created": true
}
```

**Cancel pending action:**
```
POST /api/ai/assistant/cancel
Content-Type: application/json
X-CSRF-TOKEN: <token>

Response:
{
  "reply": "Action cancelled.",
  "history": [...]
}
```

---

## 🛠️ Project Structure

```
finko/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AIChatController.php          # Chatbot endpoint
│   │       ├── AIAssistantController.php     # CRUD endpoint
│   │       ├── BudgetController.php
│   │       ├── CategoryController.php
│   │       └── TransactionController.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Budget.php
│   │   ├── Category.php
│   │   └── Transaction.php
│   │
│   └── Services/
│       ├── AIService.php                # Ollama HTTP client
│       ├── AIInquiryService.php         # Chatbot query logic
│       ├── AIAssistantService.php       # CRUD execution logic
│       ├── AIContextState.php           # Session context manager
│       └── SupabaseAuthService.php
│
├── resources/
│   ├── js/
│   │   ├── app.js                       # Main JS entry
│   │   ├── ai-chat-widget.js            # Chat widget (main logic)
│   │   └── bootstrap.js
│   │
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php            # Main layout with widget
│       │
│       └── partials/
│           ├── ai-chat-widget.blade.php # Chat widget template
│           └── ...other partials
│
├── routes/
│   ├── api.php                          # AI endpoints
│   ├── web.php                          # CRUD routes
│   └── console.php
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── config/
│   ├── ai.php                           # AI provider settings
│   └── ...
│
├── .env.example
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
└── README.md
```

---

## 📋 Implementation Checklist

### Minimum Requirements (Lab 3)
- [x] **AI Chatbot for Inquiries**
  - [x] Accept natural language queries
  - [x] Retrieve and process data
  - [x] Provide conversational responses
  - [x] Handle 5+ types of inquiries
  - [x] Maintain conversation context (10 messages)
  - [x] Graceful error handling

- [x] **Dummy Data**
  - [x] 20+ sample records
  - [x] Realistic and diverse data
  - [x] Multiple categories and statuses
  - [x] Database seeders for automation

- [x] **On-Page Chat Interface**
  - [x] Floating widget on main page
  - [x] Message history display
  - [x] Input field with send button
  - [x] Loading indicators
  - [x] Error messages
  - [x] Toggle button

### Expanded Requirements (Lab 3+)
- [x] **AI Assistant for CRUD Operations**
  - [x] Parse natural language to CRUD
  - [x] Function calling pattern
  - [x] Create operations
  - [x] Read operations
  - [x] Update operations
  - [x] Delete operations (with confirmation)
  - [x] Destructive operation confirmation
  - [x] Success/failure feedback
  - [x] Updated data shown on main page

- [x] **Intelligent Context Awareness**
  - [x] Multi-message conversation context
  - [x] Follow-up question handling
  - [x] Pronoun reference handling
  - [x] Session memory of preferences
  - [x] Last 10+ messages history

### Code Quality (Best Practices)
- [x] Proper error handling
- [x] Rate limiting awareness
- [x] Efficient database queries
- [x] Clean separation of concerns
- [x] Well-organized project structure

---

## 🧪 Testing the Implementation

### Test Chatbot Mode (5+ queries)

**Query 1: List Categories**
```
User: "What categories do I have?"
Expected: List all categories with count
```

**Query 2: Count Items**
```
User: "How many categories are there?"
Expected: Returns exact count
```

**Query 3: Recent Transactions**
```
User: "Show my latest transactions"
Expected: Lists 10 most recent transactions
```

**Query 4: Financial Summary**
```
User: "How much did I spend this week?"
Expected: Shows weekly expense total
```

**Query 5: Highest Spending**
```
User: "What's my biggest expense?"
Expected: Shows transaction with highest amount
```

### Test Assistant Mode (CRUD)

**Create Test:**
```
User: "Create a category named 'Test' as expense"
AI: Creates category
Result: ✓ Category appears on categories page
```

**Update Test:**
```
User: "Update the category to income"
AI: [Confirm?]
User: ✓ Confirm
Result: ✓ Category type changed, page refreshes
```

**Delete Test:**
```
User: "Delete the 'Test' category"
AI: [Confirm?]
User: ✓ Confirm
Result: ✓ Category removed, page refreshes
```

### Test Context Awareness

**Follow-up Test:**
```
User: "Show my budgets"
AI: [Lists budgets]

User: "Which ones are over 5000?"
AI: [Filters previous results]

User: "How much is allocated in total?"
AI: [Sums the filtered budgets]
```

---

## 🎨 UI/UX Improvements Made

### Typography & Colors
- Clean sans-serif font (Tailwind defaults)
- Consistent color scheme (emerald primary, slate secondary)
- High contrast for accessibility
- Proper font sizes and weights

### Spacing & Layout
- 4px/8px/12px/16px spacing system
- Proper padding inside messages
- 3px vertical spacing between message groups
- Aligned icons with text

### Interactions
- Hover states on all interactive elements
- Active states with scale/color change
- Focus rings for keyboard navigation
- Disabled states for loading

### Animations
- Fade-in on panel open
- Slide-in on new messages
- Bounce animation on typing dots
- Scale effects on button hover

### Accessibility
- Proper ARIA labels and roles
- Keyboard navigation (Tab, Enter, Escape)
- Screen reader support
- Color not only indicator of state
- Sufficient contrast ratios

---

## 🐛 Troubleshooting

### Ollama Not Connecting
**Error:** `Unable to connect to the AI service`

**Solution:**
1. Verify Ollama is installed from https://ollama.com
2. Run `ollama serve` in terminal
3. Check `http://127.0.0.1:11434` is accessible
4. Verify model is pulled: `ollama list`

### Chat Widget Not Appearing
**Error:** Widget doesn't show up

**Solution:**
1. Ensure you're logged in (widget only visible for authenticated users)
2. Open browser console (F12) for JavaScript errors
3. Run `npm run dev` to rebuild assets
4. Clear browser cache and reload
5. Check `resources/views/layouts/app.blade.php` includes the widget partial

### Database Connection Issues
**Error:** `connection to server failed`

**Solution:**
1. Verify `.env` database credentials
2. Check Supabase instance is running
3. Test connection: `php artisan tinker` → `DB::connection()->getPdo()`
4. Ensure network can reach Supabase

### Slow AI Responses
**Error:** Chat takes 30+ seconds

**Solution:**
1. Ensure `qwen2.5:3b` model is downloaded
2. Check system resources (RAM, CPU usage)
3. Reduce `AI_REQUEST_TIMEOUT_SECONDS` to 15
4. Use smaller model if available
5. Restart Ollama service

---

## 📚 Code References

### Key Files for AI Integration

| File | Purpose | Lines |
|------|---------|-------|
| [routes/api.php](routes/api.php) | API endpoints definition | ~20 |
| [app/Http/Controllers/AIChatController.php](app/Http/Controllers/AIChatController.php) | Chatbot request handler | ~150 |
| [app/Http/Controllers/AIAssistantController.php](app/Http/Controllers/AIAssistantController.php) | Assistant handler | ~200 |
| [app/Services/AIService.php](app/Services/AIService.php) | Ollama client | ~80 |
| [app/Services/AIInquiryService.php](app/Services/AIInquiryService.php) | Chatbot logic | ~400 |
| [app/Services/AIAssistantService.php](app/Services/AIAssistantService.php) | CRUD logic | ~300 |
| [app/Services/AIContextState.php](app/Services/AIContextState.php) | Context manager | ~100 |
| [resources/js/ai-chat-widget.js](resources/js/ai-chat-widget.js) | Frontend logic | ~500 |
| [resources/views/partials/ai-chat-widget.blade.php](resources/views/partials/ai-chat-widget.blade.php) | Widget HTML | ~150 |
| [config/ai.php](config/ai.php) | AI configuration | ~50 |

---

## 🎓 Learning Outcomes

By completing this lab, you should understand:

1. **AI Integration in Web Apps** - How to integrate LLMs into full-stack applications
2. **Backend Proxy Pattern** - Why direct database access from AI is dangerous
3. **Prompt Engineering** - Crafting effective system prompts for specific tasks
4. **Intent Classification** - Using LLMs to understand user intent
5. **Conversation Context** - Managing state and history across multiple turns
6. **Function Calling** - Mapping natural language to database operations
7. **Error Handling** - Gracefully handling AI and API failures
8. **User Confirmation** - Safe patterns for destructive operations
9. **Chat UI/UX** - Professional-grade conversational interface design
10. **Security Best Practices** - Protecting sensitive data in AI applications

---

## 📞 Support

**Having Issues?**
1. Check [Troubleshooting](#-troubleshooting) section above
2. Review [API Endpoints](#-api-endpoints) for request/response format
3. Check browser console (F12) for JavaScript errors
4. Check Laravel logs: `storage/logs/laravel.log`
5. Run: `php artisan tinker` to test database connection

**Resources:**
- [Laravel Docs](https://laravel.com/docs)
- [Ollama Docs](https://github.com/ollama/ollama)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [CMSC 129 Lab Guide](../README.md)

---

## 📜 License

Student project for CMSC 129. Free to fork and use for learning.

---

**Version:** 1.0.0  
**Last Updated:** April 27, 2026  
**Status:** Complete & Production-Ready ✅
