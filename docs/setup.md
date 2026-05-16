# Setup Guide

This document should stay current as the project evolves.

## Expected local stack

- PHP 8.4 or 8.5
- Composer
- Node.js
- PostgreSQL
- Redis
- Laravel Sail or local services

## Initial setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## With Sail

```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm run dev
```

## Useful commands

```bash
php artisan test
vendor/bin/pint
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan queue:work
php artisan schedule:work
```

## Boost

After installing or changing packages:

```bash
php artisan boost:update --discover
php artisan boost:skill-list
```
