# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 13 REST API backend for a hackathon project. Uses Laravel Sanctum for token-based authentication. The default database is SQLite (local dev), with Laravel Sail (Docker) for containerized development.

## Commands

### Setup
```bash
composer run setup        # Install deps, copy .env, generate key, migrate, build assets
```

### Development
```bash
composer run dev          # Start all services: HTTP server, queue, log watcher, Vite
# or with Sail:
./vendor/bin/sail up -d
```

### Testing
```bash
composer run test         # Clear config cache, then run PHPUnit
php artisan test --filter TestName   # Run a single test
```

### Code Style
```bash
./vendor/bin/pint         # Laravel Pint (PSR-12 code style fixer)
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh  # Drop all tables and re-run migrations
php artisan tinker         # Interactive REPL
```

## Architecture

### API Structure
All routes are in `routes/api.php`, prefixed at `/api/`. Authentication uses Sanctum bearer tokens — protected routes use `middleware('auth:sanctum')`.

Current endpoints:
- `POST /api/register` → `AuthController@register` (returns user + token)
- `POST /api/login` → `AuthController@login` (returns user + token)
- `POST /api/logout` → `AuthController@logout` (requires Bearer token, deletes all tokens)

### Controllers
`app/Http/Controllers/` — controllers extend the base `Controller`. `AuthController` handles registration, login, and logout. Error messages may be in Hungarian (`"Hibás email vagy jelszó."`).

### Models
`app/Models/User.php` uses PHP 8 attribute syntax (`#[Fillable(...)]`, `#[Hidden(...)]`) alongside the traditional `$fillable` array. The `HasApiTokens` trait (Sanctum) is required for token issuance.

### Database
Default connection is SQLite (`database/database.sqlite`). Sail compose file (`compose.yaml`) has no external DB service — SQLite is used locally. To switch to MySQL/PostgreSQL, update `.env` and `compose.yaml`.

### HTTP Request Files
`app/Http/Controllers/api.http` contains sample HTTP requests for manual API testing (usable in PHPStorm/VS Code REST Client).