# ShareCart – Grocery List App – Project Plan

## Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** HTML, Blade, CSS, Bootstrap 5 (no JS frameworks; vanilla JS for Ajax)
- **Database:** MySQL

## Database Structure
- **users** – Authentication (existing via Laravel Breeze)
- **lists** – Grocery lists; each has an owner (`user_id`), and can be shared
- **list_items** – Items belonging to a list (`list_id`, name, quantity, etc.)
- **list_user** – Pivot: which users have access to which lists (sharing)

---

## Phase 1: User registration and login ✅
- **Status:** Already in place via Laravel Breeze.
- **Optional:** Ensure MySQL is configured in `.env`; keep session-based auth for web UI.
- **Next:** Add Bootstrap to layouts and auth views for a consistent UI.

---

## Phase 2: CRUD for lists and list items
- **Migrations:** `lists` (id, user_id, name, timestamps), `list_items` (id, list_id, name, quantity, completed, timestamps), `list_user` (list_id, user_id).
- **Models:** `GroceryList` (table: `lists`), `ListItem`, relations on `User`.
- **Controllers:** `GroceryListController` (index, create, store, show, edit, update, destroy), `ListItemController` (store, update, destroy).
- **Validation:** Form Request classes with rules and sanitization for all inputs.
- **Authorization:** Policies so users can only manage their own lists (and shared lists where allowed).
- **Routes:** Resource routes for lists; nested or separate routes for items.

---

## Phase 3: Real-time updates (Ajax polling)
- **Endpoint:** e.g. `GET /api/lists/{list}/poll` (or `/lists/{list}/items/json`) returning current list + items as JSON.
- **Front-end:** Vanilla JS `setInterval` that calls this endpoint every few seconds when viewing a list; compare `updated_at` or version to avoid unnecessary DOM updates.
- **Security:** Ensure poll endpoint checks that the authenticated user can access the list (owner or shared).

---

## Phase 4: Responsive UI with Bootstrap
- **Layout:** Replace Tailwind in app layout with Bootstrap 5 (CDN or built asset).
- **Pages:** Dashboard (list of user’s lists), list show (items with add/edit/delete), forms for create/edit list and add/edit item.
- **Design:** Simple, clean, mobile-first; use Bootstrap grid, cards, forms, buttons, modals for delete confirmations.
- **Navigation:** Navbar with links to “My lists”, profile, logout.

---

## Phase 5: Suggestion system
- **Backend:** Service or controller method that queries `list_items` (e.g. aggregate by normalized item name), counts frequency across all lists (or only shared/community), returns top N suggestions.
- **Endpoint:** e.g. `GET /suggestions` or `/lists/suggestions` returning JSON for typeahead, or a “suggested items” block on the add-item form.
- **Privacy:** Consider whether to use all lists or only the current user’s lists; document the choice (e.g. “suggestions from all users’ lists”).
- **Performance:** Optional cache (Redis or Laravel cache) for suggestion list, invalidated when items change.

---

## Phase 6: Testing and deployment
- **Testing:** Manual testing on desktop and mobile (Chrome, Safari, Firefox); test auth, CRUD, sharing, polling, suggestions.
- **Security:** CSRF on all forms; validated inputs; authorization on every list/item action; avoid mass assignment.
- **Deployment:** Configure production `.env` (APP_ENV=production, MySQL, APP_DEBUG=false); run migrations; set document root to `public/`; HTTPS recommended.

---

## File and code conventions
- **Modular:** Controllers thin; business logic in Form Requests, Policies, and optional Service classes (e.g. `SuggestionService`).
- **Comments:** DocBlocks on controllers and services; short inline comments for non-obvious logic.
- **Validation:** All user input via Form Requests with clear rules and messages.
- **Scalability:** Structure so new features (e.g. categories, due dates) can be added without rewriting core code.

---

## Implementation order (this repo)
1. ✅ PROJECT_PLAN.md (this file)
2. Migrations: `lists`, `list_items`, `list_user`
3. Models: `GroceryList`, `ListItem`; relations and `User` updates
4. Policies and Form Requests
5. Controllers and routes
6. Bootstrap layout and views (dashboard → lists → list detail with items)
7. Ajax polling endpoint and script
8. Suggestion service and endpoint
9. Final testing notes and deployment checklist
