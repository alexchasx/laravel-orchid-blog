# AGENTS.md

Russian-language IT blog. **Laravel 13 (PHP ^8.5)** backend with **Orchid Platform 14** admin panel at `/admin` (`app/Orchid/`), plus a single public frontend — **Blade/Vite — "TECH//LOG"** (`resources/views/`, `layouts.techlog`).

The branding/design direction is documented in `docs/design-update-plan.md`. Auth (Breeze) and comments are Blade-only. When adding a public feature, decide which surface it belongs to: Blade view, admin panel, or both.

## Language

UI copy, code comments, and all docs are in **Russian** — write new ones in Russian.

## Commands

The project runs **Docker-only** — no local php/composer/npm/artisan. `make` drives all commands through Docker Compose from **`docker/docker-compose.yml`** (not repo root); `make help` lists everything:

- `make install` — one-command Docker setup: `.env` (from `.env.example`), build + up containers, composer/npm installs, `key:generate`, **`migrate:fresh --seed`**, Orchid admin (`admin@localhost.ru`/`123456`), `storage:link`, frontend build
- `make up` / `make down` / `make logs` / `make shell` — container lifecycle
- `make migrate` — runs **`migrate:fresh --seed`** (destructive)
- `make orchid-admin` — creates admin user (`admin@localhost.ru` / `123456`; non-interactive)
- `make test` — `php artisan test` **inside `blog_app`**; `make lint` — `php -l` only (no phpstan, no Pint config)
- `make frontend-build` / `make frontend-dev` — `npm run build` / Vite dev server **inside `blog_node`**

Defaults: site `:8080`, admin `:8080/admin`, phpMyAdmin `:8899`, MailHog `:8026`, Vite dev `:5173`; DB `laraorchid`/`root`/`root` (matches `docker/docker-compose.yml` and `.env.example`).

No CI. Tests are currently only boilerplate `ExampleTest`; add real tests when touching domain logic (PHPUnit runs against the `testing` DB per `phpunit.xml`).

## Gotchas

- **DB column is `excert` (typo, kept).** `Article` model, `ArticleService`, and `ArticleResource` all reference `excert`.
- **Cache invalidation happens via model observers** (`ArticleObserver`, `RubricObserver`, `TagObserver`) and `Tag::updateCountArticles()`; cache keys like `Tag::SIDEBAR_CACHE_KEY` live on models. There are **no Events/Listeners** — keep the observer pattern.
- `Article::published()` scope = `is_published` && `published_at <= now`; `ArticleService::checkAccess()` returns 403 for non-admins viewing unpublished articles.
- `Article` auto-generates `slug` and converts markdown `content_raw` → `content_html` on save (model `booted()`).
- `GoogleRecaptcha` middleware exists but is **not wired** to any route; if you enable it on contact/comments, requests without an `r` field get 403 (forms must send it).
- Laravel 13-style layout: routing/middleware are registered in `bootstrap/app.php` (no `Http/Kernel.php`). Custom `Localize` middleware is appended to `web`; `access` alias → Orchid Access middleware.
- Env-driven site config lives in `config/my_config.php` (`MY_GITHUB`, `MY_EMAIL`, `MY_TELEGRAM`, `CONTACT_EMAIL`, `SUB_LOGO`). `app/helpers.php` is autoloaded via composer `autoload-dev.files`.