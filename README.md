# PHP Starter

A project starter built on **Maestro**, a self-written lightweight PHP 8.2 MVC framework. It ships with an index page, a login page, and an admin area for managing users via magic-link invites.

## Features

- Index page with header and footer
- Email + password login
- Magic-link invite flow — admin creates a user, shares the invite link, user sets their own password
- Admin user management: create, edit, resend invites
- Role-based access control (`admin` / `user`)
- Profile page (change name, email, password)

---

## Maestro Framework

Built on **Maestro**, a self-written lightweight PHP 8.2 MVC framework using:

- **Twig** for templating (Tailwind CSS + Alpine.js in views)
- **PDO** for database access (no ORM)
- **Repository pattern** for data access
- A simple **dependency injection container**
- A **regex-based router** with named parameters

Namespace layout:

| Namespace | Directory | Role |
| --- | --- | --- |
| `Framework\` | `src/` | Core framework (Router, Kernel, Database, …) |
| `App\` | `app/` | Application code (Controllers, Models, Repositories, Views) |

---

## Development

### Requirements

- PHP 8.2+
- Composer
- Docker (for MySQL)

### Setup

#### 1. Start the database

```bash
docker compose up -d
```

This starts **MySQL 8.4** on port `3306` with:

| Setting | Value |
| --- | --- |
| Database | `maestro` |
| User | `maestro` |
| Password | `secret` |
| Root password | `rootsecret` |

Data is persisted in a named Docker volume (`mysql_data`), so it survives container restarts.

#### 2. Install PHP dependencies

```bash
composer install
```

#### 3. Run database migrations

```bash
php maestro migrate
```

This runs all `.sql` files in `database/` alphabetically, creating the schema and seeding the two roles (`admin`, `user`). It also creates a default admin account if no users exist yet:

| | |
| --- | --- |
| Email | `admin@localhost` |
| Password | `admin` |

#### 4. Start the development server

```bash
php maestro serve
```

Open [http://localhost:8888](http://localhost:8888).

### Database configuration

Connection settings are read from environment variables with defaults that match the Docker setup:

| Variable | Default |
| --- | --- |
| `APP_DB_DSN` | `mysql:host=127.0.0.1;port=3306;dbname=maestro;charset=utf8mb4` |
| `APP_DB_USER` | `maestro` |
| `APP_DB_PASS` | `secret` |

To override, export the variables before running:

```bash
export APP_DB_DSN="mysql:host=db.example.com;port=3306;dbname=mydb;charset=utf8mb4"
export APP_DB_USER="myuser"
export APP_DB_PASS="mypassword"
php maestro serve
```

### Commands

| Command | Description |
| --- | --- |
| `php maestro serve` | Start dev server on port 8888 |
| `php maestro migrate` | Run all SQL migrations |
| `php maestro phpcs` | Check code style (PSR via phpcs) |
| `php maestro phpstan` | Run static analysis (level 8) |
| `php maestro deptrac` | Check architecture layer rules |

### Project structure

```text
app/
  Controllers/        HTTP handlers
    AuthController    Login, logout, invite flow, profile
    HomeController    Index page
    UserController    Admin user management
  Models/             Plain data objects (User, Role)
  Repositories/       Data-access layer (UserRepository + interface)
  Views/
    partials/         base.html.twig (header + footer layout)
    auth/             login, set_password, profile templates
    users/            admin user management template
    index.html.twig   Welcome page
src/
  Kernel.php          Bootstrap: wires container, database, router
  Router.php          Regex-based routing
  Database.php        PDO wrapper
  ServiceContainer.php  Simple DI container
database/
  0_reset.sql         Drop all tables
  1_create_tables.sql Schema (role, user)
  6_auth_setup.sql    Seed roles
public/
  index.php           Single entry point
  .htaccess           URL rewriting (Apache)
maestro               CLI tool
docker-compose.yml    MySQL 8.4 container
```

### Routes

| Method | Path | Description |
| --- | --- | --- |
| GET | `/` | Index page |
| GET | `/login` | Login form |
| POST | `/login` | Authenticate |
| POST | `/logout` | Sign out |
| GET | `/invite/{token}` | Set-password page (magic link) |
| POST | `/invite/{token}` | Activate account |
| GET | `/profile` | Edit profile (auth required) |
| POST | `/profile` | Save profile (auth required) |
| GET | `/admin/users` | User list (admin only) |
| POST | `/admin/users` | Create user + generate invite (admin only) |
| POST | `/admin/users/{id}` | Update user (admin only) |
| POST | `/admin/users/{id}/invite` | Resend invite link (admin only) |

---

## Production

### Production requirements

- PHP 8.2+ with `pdo_mysql` extension
- Apache with `mod_rewrite` enabled (or Nginx with equivalent config)
- MySQL 5.7+ (8.4 recommended)
- SSH access (recommended, for running migrations)

### 1. Build for production

```bash
composer install --no-dev --optimize-autoloader
```

### 2. Upload files

Only `public/` is the web root — everything else must live **above** it.

| Upload | To |
| --- | --- |
| `app/` | above document root |
| `src/` | above document root |
| `vendor/` | above document root |
| `database/` | above document root |
| `public/index.php` | document root (`public_html/` or equivalent) |
| `public/.htaccess` | document root |

A typical shared-host layout:

```text
~/
  app/
  src/
  vendor/
  database/
  public_html/       ← document root
    index.php
    .htaccess
```

### 3. Configure the database connection

**Option A — environment variables** (if the host supports `SetEnv` in `.htaccess`):

```apache
SetEnv APP_ENV     "production"
SetEnv APP_DB_DSN  "mysql:host=your-host;dbname=your_db;charset=utf8mb4"
SetEnv APP_DB_USER "your_db_user"
SetEnv APP_DB_PASS "your_db_password"
```

**Option B — edit `public/index.php` directly:**

```php
$config = array(
    'APP_ENV'     => 'production',
    'VIEWS_PATH'  => 'app/views',
    'APP_DB_DSN'  => 'mysql:host=your-host;dbname=your_db;charset=utf8mb4',
    'APP_DB_USER' => 'your_db_user',
    'APP_DB_PASS' => 'your_db_password',
);
```

### 4. Run migrations

**With SSH access:**

```bash
php maestro migrate
```

**Without SSH (phpMyAdmin or similar):** run the files in `database/` manually in alphabetical order.

### 5. Change the default admin password

Log in at `/login` with `admin@localhost` / `admin` and update your password at `/profile` immediately.

---

## Claude Code integration

This project includes a [CLAUDE.md](CLAUDE.md) with architecture notes for Claude Code, and a `.claude/settings.json` that configures a hook to automatically run `phpcs` after every PHP file edit.
