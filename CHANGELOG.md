# Changelog

All notable changes to this package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this package adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-08-01

### Added

- Consistent JSON response contract for success, error, and paginated responses
- `ApiSuccessResponse` and `ApiErrorResponse` — controller-facing response factories
- Exception → JSON handling via `Handler.php` stub — all exceptions return JSON, never HTML
- Module-aware localization — `gui` messages resolved from `lang/{locale}/api_results.php` by module and key
- `MessageKeyEnum` — standard vocabulary for CRUD and status action keys
- `MessageResolver`, `ModuleResolver`, `LocalizationResolver` — automatic key and module resolution
- `ApiPaginator` for Eloquent paginators, `ArrayPaginator` for plain arrays and collections
- Middleware stack: `ForceAcceptJson`, `BindRequestContext`, `WrapApiResponse`, `AppendExecutionTimeMeta`, `ForceContentType`
- Structured logging via `ApiLogger` — separate channels for errors, rendered responses, and missing translations
- `DomainLayerException` and `ItemNotFoundException` — ready-to-throw domain exception classes
- Request macros: `$request->isApi()` and `$request->apiModule()`
- Artisan commands: `api-response:install` and `api-response:check [--fix]`
- Publishable config: `api_response.php` and `api_response_logging.php`
- Built-in translations: `en/` and `ru/`
