# Laravel API Response

A Laravel package for building consistent, well-structured JSON REST API responses — with automatic exception handling, module-aware localization, structured logging, and pagination out of the box.

[![Tests](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml)
[![Code Style](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml)
[![Static Analysis](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml)
[![Latest Stable Version](https://poser.pugx.org/src83/laravel-api-response/v)](https://packagist.org/packages/src83/laravel-api-response)
[![License](https://poser.pugx.org/src83/laravel-api-response/license)](LICENSE.md)

---

## Table of contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Features](#features)
- [Documentation](#documentation)
- [Changelog](CHANGELOG.md)
- [License](#license)

---

## Introduction

`laravel-api-response` enforces a single, predictable JSON contract across your entire API — success responses, error responses, paginated collections, and exceptions all share the same shape.

Every response carries a structured `message` object with a machine-readable `key` and a human-readable `gui` string resolved from your translation files. Errors additionally include a `sys` field for internal context.

```json
{
    "success": true,
    "http_code": 201,
    "http_text": "Created",
    "message": {
        "key": "user.created",
        "gui": "User created successfully"
    },
    "meta": null,
    "data": {}
}
```

```json
{
    "success": false,
    "http_code": 422,
    "http_text": "Unprocessable Content",
    "message": {
        "key": "user.unprocessable_content",
        "gui": "Validation error",
        "sys": "..."
    },
    "details": {}
}
```

## Requirements

| | |
|---|---|
| PHP | 8.2 or higher |
| Laravel | 9, 10, or 11 |

## Installation

```bash
composer require src83/laravel-api-response
php artisan api-response:install
```

The install command patches `Kernel.php`, `phpunit.xml`, `.env`, and `.env.example`. Two files require manual attention — `Handler.php` and `Authenticate.php`. Run the check command to see their status and apply stubs automatically:

```bash
php artisan api-response:check
php artisan api-response:check --fix
```

Full installation guide → [docs/installation.md](docs/installation.md)

## Quick start

```php
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiSuccessResponse;
use Symfony\Component\HttpFoundation\Response;

// List (no messageKey)
return ApiSuccessResponse::make(UserResource::collection($users));

// Store / Update / Destroy
return ApiSuccessResponse::make(new UserResource($user), MessageKeyEnum::CREATED);

// Error
return ApiErrorResponse::make(Response::HTTP_NOT_FOUND, 'user.not_found');
```

## Features

- **Consistent JSON contract** — every response, including exceptions, returns the same structure
- **Exception → JSON** — HTTP and domain exceptions are caught and rendered as structured errors, never as HTML
- **Module-aware localization** — `gui` messages are resolved from `lang/{locale}/api_response.php` by module and key
- **Pagination** — built-in `ApiPaginator` for Eloquent paginators and `ArrayPaginator` for plain arrays
- **Structured logging** — separate channels for throwable errors, rendered errors, missing translations, and business warnings
- **Middleware stack** — `ForceAcceptJson`, `BindRequestContext` (request ID), `WrapApiResponse`, `AppendExecutionTimeMeta`, `ForceContentType`
- **Execution time** — optional `meta.execution_time` field for diagnosing slow endpoints
- **Artisan commands** — `api-response:install` and `api-response:check --fix`

## Documentation

> Documentation is being written. Links will be updated before the first stable release.

- Installation & configuration → [docs/installation.md](docs/installation.md)
- JSON response contract → `docs/contract.md`
- Localization → `docs/localization.md`
- Logging → `docs/logging.md`
- Exception handling → `docs/exceptions.md`
- Pagination → `docs/pagination.md`

## License

- Written and copyrighted &copy; 2026 by [Roman Staroseltsev](https://github.com/src83).
- Open-source software licensed under the [MIT license](LICENSE.md).
