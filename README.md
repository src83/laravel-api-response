# Unified REST API Response formatter for Laravel

`LaravelApiResponse` is a [Laravel](https://laravel.com/) package for building consistent and well-structured REST API JSON responses.
Includes automatic exception handling, module-aware localization, structured logging, and pagination out of the box.

[![Tests](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml)
[![Code Style](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml)
[![Static Analysis](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml)
[![Latest Stable Version](https://poser.pugx.org/src83/laravel-api-response/v)](https://packagist.org/packages/src83/laravel-api-response)
[![License](https://poser.pugx.org/src83/laravel-api-response/license)](LICENSE.md)

**Available in other languages:** [RU](docs/ru/README.md)

---

## Table of contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Features](#features)
- [Design principles](#design-principles)
- [Documentation](#documentation)
- [Changelog](CHANGELOG.md)
- [License](#license)

---

## Introduction

Every Laravel API developer has hit the same wall: an exception fires, and instead of JSON your client gets a wall of HTML. `laravel-api-response` fixes that — and while it's at it, gives every response a consistent, predictable structure your frontend can always rely on.

Every response carries a structured `message` object with a machine-readable `key` and a human-readable `gui` string resolved from your translation files. Errors additionally include a `sys` field for internal context.

**Success response example:**

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
    "data": {
        "id": 42,
        "email": "user@example.com"
    }
}
```

**Error response example** (including exceptions — always JSON, never HTML):

```json
{
    "success": false,
    "http_code": 422,
    "http_text": "Unprocessable Content",
    "message": {
        "key": "user.unprocessable_content",
        "gui": "Validation error",
        "sys": "The email field is required"
    },
    "details": {
        "fields": {
            "email": ["The email field is required."]
        }
    }
}
```

`data` and `details` are mutually exclusive — `data` and `meta` appears in **success** responses, `details` in **errors**. `meta` is `null` for simple responses and carries pagination data when the response is paginated.

## Requirements

|         | Versions      |
|---------|---------------|
| PHP     | 8.2 or higher |
| Laravel | 9, 10, or 11  |

## Installation

**1. Require the package:**

```bash
composer require src83/laravel-api-response
```

**2. Run the installer:**

```bash
php artisan api-response:install
```
The installer adds some things and modifies others in your existing project. You can review these targeted changes by running `git status` after executing the installation command.

In particular, pay close attention to `Handler.php` and `Authenticate.php`.

This package can be installed both on a fresh Laravel application and on an existing project with substantial business logic and a customized exception handler.

Depending on the project, the installer may behave differently (adaptively):

* In a new, clean Laravel project, these two files will be fully updated during installation. However, they still require a quick review before you accept the changes. Once the installation is complete, you can immediately proceed to the next step `Verify the installation` to make sure the package was installed correctly.

* When installing the package into a more mature project, `Handler.php` and `Authenticate.php` may not be updated immediately if the installer detects that they already contain customized logic. Such items will be marked as `ACTION REQUIRED`. This is normal behavior. In any case, proceed to the next step `Verify the installation`.

**3. Verify the installation:**

```bash
php artisan api-response:check
```

Confirms that all components have been installed correctly.  
If anything shows `ACTION REQUIRED`, the installer detected existing custom logic in that file and did not overwrite it automatically.

In this case:
- Back up the files marked `ACTION REQUIRED`;
- Force-update them by running:
    ```bash
    php artisan api-response:check --fix
    ```
- Manually resolve any conflicts between the existing and new logic in the updated files.

That's it — the package is installed and ready to use.  
You can run `php artisan api-response:check` one more time to confirm, then get to the fun part.

## Quick start

```php
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiSuccessResponse;
use Symfony\Component\HttpFoundation\Response;

// List — no messageKey needed
return ApiSuccessResponse::make(data: UserResource::collection($users));

// Store
return ApiSuccessResponse::make(
    data: new UserResource($user),
    httpCode: Response::HTTP_CREATED,
    messageKey: MessageKeyEnum::CREATED,
);

// Error
return ApiErrorResponse::make(
    httpCode: Response::HTTP_NOT_FOUND,
    messageKey: 'user.not_found',
    sysMessage: $e->getMessage(),
);
```

For a full reference — call signatures, response shapes, and pagination — see the [docs/api-contract.md](docs/api-contract.md)

## Features

- **Consistent JSON contract** — every response, including exceptions, returns the same structure
- **Exception → JSON** — HTTP and domain exceptions are caught and rendered as structured errors, never as HTML
- **Module-aware localization** — `gui` messages are resolved from `lang/{locale}/api_response.php` by module and key
- **Pagination** — built-in `ApiPaginator` for Eloquent paginators and `ArrayPaginator` for plain arrays
- **Structured logging** — separate channels for throwable errors, rendered errors, missing translations, and business warnings
- **Middleware stack** — `ForceAcceptJson`, `BindRequestContext` (request ID), `WrapApiResponse`, `AppendExecutionTimeMeta`, `ForceContentType`
- **Execution time** — optional `meta.execution_time` field for diagnosing slow endpoints
- **Artisan commands** — `api-response:install` and `api-response:check [--fix]`
- **Built-in exceptions** — `DomainLayerException` and `ItemNotFoundException` ready to throw from your domain layer
- **Request macros** — `$request->isApi()` and `$request->apiModule()` available out of the box
- **Standard action vocabulary** — `MessageKeyEnum` covers all common CRUD and status keys

## Design principles

- Controllers don't build messages — they pass a `messageKey`, the package resolves the translation
- `messageKey` can be a `MessageKeyEnum` value, a plain string, or a compound `module.key`
- The module is derived automatically from the route prefix — no manual configuration needed per endpoint
- All exceptions speak the same JSON — no special cases, no HTML leaking through

## Documentation

> The essentials are covered in this README. Full documentation is in progress — for details, browse the inline 
> code comments or [open an issue](https://github.com/src83/laravel-api-response/issues).

- Installation → [docs/installation.md](docs/installation.md)
- API reference (contract + usage examples) → [docs/api-contract.md](docs/api-contract.md)
- Configuration reference → [docs/configuration.md](docs/configuration.md)
- Localization → [docs/localization.md](docs/localization.md)
- Logging → [docs/logging.md](docs/logging.md)
- Exception handling → [docs/exceptions.md](docs/exceptions.md)

## License

- Written and copyrighted &copy; 2026 by [Roman Staroseltsev](https://github.com/src83)
- Open-source software licensed under the [MIT license](LICENSE.md)
- Author: [LinkedIn](https://linkedin.com/in/staroseltsev)
