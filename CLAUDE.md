# Maestro — IT Learning Outcomes

## After making code changes

After completing any set of PHP code changes, always run these two tools before finishing:

```bash
php maestro phpstan   # type errors and unsafe code (whole codebase, level 8)
php maestro deptrac   # architecture layer violations (App → Framework → Vendor)
```

`phpcs` (code style) runs automatically via a Claude Code hook after every file edit — no need to run it manually.

A self-written lightweight PHP 8.2 MVC framework with a repository pattern, Twig templating, and SQLite database.

## Architecture

```text
src/       Framework layer (Framework\ namespace)
app/       Application layer (App\ namespace)
  Controllers/   HTTP handlers
  Models/        Plain data objects (Project, Task, Tag)
  Repositories/  Data-access layer (wraps Database)
  views/         Twig templates
public/    Web root — index.php is the single entry point
database/  SQL migration files (run in alphabetical order)
```

Dependency flow enforced by deptrac: `App → Framework → Vendor (Twig)`.

## Database

**Engine:** SQLite 3 (file-based, no server required)

**Database file:** `database/maestro.sqlite` (created on first migrate)

**Connection config** — read from environment variables with fallbacks:

| Variable | Default |
| --- | --- |
| `APP_DB_DSN` | `sqlite:<project-root>/database/maestro.sqlite` |
| `APP_DB_USER` | *(empty)* |
| `APP_DB_PASS` | *(empty)* |

Override by exporting `APP_DB_DSN` or editing `public/index.php` / `maestro` directly.

**Schema:** `projects` → `tasks` (one-to-many), `tasks` ↔ `tags` (many-to-many via `task_tags`).

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install frontend dependencies
cd frontend && npm install && cd ..

# 3. Run migrations (creates database/maestro.sqlite)
php maestro migrate

# 4. Start dev (PHP API on :8888, Vite on :5173 with HMR)
php maestro dev
```

## Development commands

```bash
php maestro migrate   # Run all SQL files in database/ alphabetically
php maestro serve     # PHP built-in server on port 8888 (no Vite)
php maestro dev       # PHP API (:8888) + Vite dev server (:5173) together
php maestro build     # Compile Svelte → public/build/ for production
php maestro phpstan   # Static analysis (level 8)
php maestro phpcs     # PSR code-style check
php maestro deptrac   # Architecture layer check
```

## Frontend

Source lives in `frontend/src/`. Vite compiles to `public/build/` (git-ignored — run `php maestro build` before deploying).

During dev, Vite proxies `/api/*` requests to the PHP server at `:8888`.

## Key files

- [src/Database.php](src/Database.php) — PDO wrapper; constructor takes `(string $dsn, string $username, string $password)`
- [src/Kernel.php](src/Kernel.php) — bootstraps container, database, router
- [src/Router.php](src/Router.php) — regex-based routing with named capture groups
- [src/ServiceContainer.php](src/ServiceContainer.php) — simple DI container
- [app/ServiceProvider.php](app/ServiceProvider.php) — wires repositories and controllers
- [app/RouteProvider.php](app/RouteProvider.php) — registers all routes
- [public/index.php](public/index.php) — single entry point; holds config array
- [maestro](maestro) — CLI tool for migrations, server, and linting

## Notes

- No ORM — raw PDO queries in Repository classes
- Migrations run alphabetically: `0_reset.sql` drops tables, `1_create_tables.sql` creates them
- Static analysis runs at PHPStan level 8 (strictest)
