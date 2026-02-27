# ShareCart

A web app for creating, sharing, and collaboratively managing grocery lists. Built with **Laravel** (backend), **HTML/CSS/Bootstrap** (frontend), and **MySQL**.

## Features

- **User auth** – Register and log in (Laravel Breeze).
- **Lists** – Create, rename, and delete multiple grocery lists.
- **List items** – Add, edit (name, quantity, completed), and remove items.
- **Sharing** – Share a list with another user by email; they get full read/write access.
- **Real-time sync** – When viewing a list, the page polls every 3 seconds so changes from you or collaborators appear without refresh.
- **Suggestions** – While adding an item, typeahead suggests popular item names from all users’ lists.

## Requirements

- PHP 8.2+
- Composer
- MySQL
- (Optional) Node/npm if you later add front-end build steps

## Setup

1. **Configure environment**
   - Copy `.env.example` to `.env`.
   - Set `DB_CONNECTION=mysql` and your MySQL credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

2. **Install and run**
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   ```

3. **Run the app**
   ```bash
   php artisan serve
   ```
   Open http://localhost:8000. Register, create lists, add items, and share a list with another account to test collaboration and polling.

## Project structure (high level)

- `app/Models/` – `User`, `GroceryList` (table: `lists`), `ListItem`.
- `app/Http/Controllers/` – `GroceryListController`, `ListItemController`, `ListPollController`, `SuggestionController`.
- `app/Services/SuggestionService.php` – Aggregates item names for suggestions.
- `app/Policies/` – `GroceryListPolicy`, `ListItemPolicy`.
- `app/Http/Requests/` – Form requests for validation (lists, items, share).
- `database/migrations/` – `lists`, `list_items`, `list_user` (sharing).
- `resources/views/` – Bootstrap layout and list/item views; polling and suggestions use vanilla JS.

See **PROJECT_PLAN.md** for the full step-by-step plan and implementation notes.

## Security

- All list and item actions are authorized via policies (owner or shared user).
- Input is validated with Form Requests.
- CSRF protection on all forms; session-based auth for web.

## Testing and deployment

- Test on desktop and mobile (list index, list detail, add/edit/delete item, share, polling, suggestions).
- For production: set `APP_ENV=production`, `APP_DEBUG=false`, configure MySQL and `APP_URL`, point document root to `public/`, use HTTPS.
