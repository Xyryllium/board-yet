## Multi-Tenant Trello Clone

A **multi-tenant Trello-style project management app** built with Laravel 12, applying DDD (Domain-Driven Design), feature tests, and organization-based multi-tenancy.

Users belong to organizations, can invite team members, and collaborate on boards, lists, and tasks.

## Features
- **Authentication & Authorization** (Laravel Sanctum)
- **Multi-Tenant Organizations**
    - Create/join organizations
    - Invite users via email (with token)
- **Invitation System**
    - Secure UUID tokens
    - Accept/Reject invitation flow
- **Boards, Lists & Cards** (Trello-style)
- **Role-based access** (Owner, Member)
- **Feature tests** for core flows
- **Fully dockerized* (Laravel, Nginx, PostgreSQL)

## Tech Stack
- **Backend:** Laravel 12 (PHP 8.3)
- **Database:** PostgreSQL
- **Dev Env:** Docker + Docker Compose
- **Testing:** Pest
- **Architecture:** Domain-Driven Design (DDD)

## Getting Started

## Clone the repo
```bash
git clone https://github.com/Xyryllium/board-yet.git
cd board-yet
````

## Start services (Docker)
```bash
docker-compose up -d
```
or:
```bash
make build
```
## Install dependencies

```bash
docker-compose exec app composer install
```
or:
```bash
make composer-install
```

### Setup env

```bash
cp .env.example .env
```

Update:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=board_yet
DB_USERNAME=postgres
DB_PASSWORD=secret
```

### Run migrations

```bash
docker-compose exec app php artisan migrate
```
or:
```bash
make migrate
```

### Run tests

```bash
docker-compose exec app php artisan test
```
or:
```bash
make test
```

## Roadmap

* [ ] Board collaboration (drag/drop lists & cards)
* [ ] Real-time updates (Laravel WebSockets)
* [ ] Teams & advanced roles
* [ ] UI with React (Separate github repo)

## Contributing

PRs are welcome! Please run tests before submitting.

## License

MIT
