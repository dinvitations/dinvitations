<p align="center"><img width="294" height="69" src="/public/logo.png" alt="Logo D'Invitations"></p>

# D'Invitations

## Introduction
D'Invitations is a web application to easily create, manage, and share digital invitations. The Dashboard application contains features for Admins and Clients to manage their invitations, orders, and other in-app data.

## Tech Stack
This is a monolith project built with Laravel Sail for its development stage. The tech stacks are but not limited to:
- **Language & Framework**
    - PHP 8.4
    - Laravel 12.0.7
- **Packages**
    - Sail ^1.42
    - Filament ^3.3
    - Grapes JS 1.1.1
- **Databases**
    - PostgreSQL 17.4
    - Redis Alpine
- **Tools**
    - Docker
    - pgAdmin 4
    - Mailpit

## Pre-requisites
- Composer and PHP 8.4, or
- Docker

## How To Install
1. Clone the repository.
    ```sh
    git clone https://github.com/dinvitations/dinvitations.git
    ```
2. Create `.env` file.
    ```sh
    cp .env.example .env
    ```
3. If you have Composer and PHP installed, run this command right away.
    ```sh
    composer require laravel/sail --dev
    ```
    Otherwise, run this command to install composer dependencies using the Laravel Sail's Docker image as suggested in the [documentation](https://laravel.com/docs/11.x/sail#installing-composer-dependencies-for-existing-projects).
    ```sh
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php84-composer:latest \
        composer install --ignore-platform-reqs
    ```
4. Configure these values in `.env` if necessary.
    ```ini
    FORWARD_DB_PORT=6379
    FORWARD_REDIS_PORT=5432
    DB_HOST=dinvitations-pgsql-1
    ```
5. Start Sail.
    ```sh
    ./vendor/bin/sail up
    ```
6. Generate Laravel application key.
    ```sh
    ./vendor/bin/sail artisan key:generate
    ```
7. If not exist, create a database as specified in `.env`. You can use pgAdmin on [localhost:5050](http://localhost:5050)
8. Run migrations and seeder
    ```sh
    ./vendor/bin/sail artisan migrate --seed
    ```
9. Optionally, you can add the `sail` command alias at the end of file `.bashrc` or `.zshrc`.
    ```rc
    alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

    ```
