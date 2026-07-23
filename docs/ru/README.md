# Laravel API Response

Laravel-пакет для формирования консистентных, хорошо структурированных JSON REST API ответов — с автоматической обработкой исключений, модульной локализацией сообщений, структурированным логированием и пагинацией из коробки.

[![Tests](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml)
[![Code Style](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml)
[![Static Analysis](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml)
[![Latest Stable Version](https://poser.pugx.org/src83/laravel-api-response/v)](https://packagist.org/packages/src83/laravel-api-response)
[![License](https://poser.pugx.org/src83/laravel-api-response/license)](../../LICENSE.md)

---

## Содержание

- [Введение](#введение)
- [Требования](#требования)
- [Установка](#установка)
- [Быстрый старт](#быстрый-старт)
- [Возможности](#возможности)
- [Документация](#документация)
- [История изменений](../../CHANGELOG.md)
- [Лицензия](#лицензия)

---

## Введение

`laravel-api-response` обеспечивает единый, предсказуемый JSON-контракт для всего API — успешные ответы, ошибки, пагинированные коллекции и исключения возвращают одну и ту же структуру.

Каждый ответ содержит структурированный объект `message` с машиночитаемым полем `key` и человекочитаемым `gui`, которое разрешается из файлов переводов. Ответы с ошибками дополнительно содержат поле `sys` для внутреннего контекста.

```json
{
    "success": true,
    "http_code": 201,
    "http_text": "Created",
    "message": {
        "key": "user.created",
        "gui": "Пользователь успешно создан"
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
        "gui": "Ошибка валидации",
        "sys": "..."
    },
    "details": {}
}
```

## Требования

| | |
|---|---|
| PHP | 8.2 и выше |
| Laravel | 9, 10 или 11 |

## Установка

```bash
composer require src83/laravel-api-response
php artisan api-response:install
```

Команда установки патчит `Kernel.php`, `phpunit.xml`, `.env` и `.env.example`. Два файла требуют ручного вмешательства — `Handler.php` и `Authenticate.php`. Запустите команду проверки чтобы увидеть их статус и при необходимости применить стабы автоматически:

```bash
php artisan api-response:check
php artisan api-response:check --fix
```

Полное руководство по установке → [docs/ru/installation.md](installation.md)

## Быстрый старт

```php
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiSuccessResponse;
use Symfony\Component\HttpFoundation\Response;

// Список (без messageKey)
return ApiSuccessResponse::make(UserResource::collection($users));

// Store / Update / Destroy
return ApiSuccessResponse::make(new UserResource($user), MessageKeyEnum::CREATED);

// Ошибка
return ApiErrorResponse::make(Response::HTTP_NOT_FOUND, 'user.not_found');
```

## Возможности

- **Единый JSON-контракт** — все ответы, включая исключения, возвращают одну структуру
- **Исключения → JSON** — HTTP и доменные исключения перехватываются и возвращаются как структурированные ошибки, никакого HTML
- **Модульная локализация** — `gui`-сообщения разрешаются из `lang/{locale}/api_response.php` по модулю и ключу
- **Пагинация** — встроенный `ApiPaginator` для Eloquent-пагинаторов и `ArrayPaginator` для обычных массивов
- **Структурированное логирование** — отдельные каналы для throwable-ошибок, rendered-ошибок, отсутствующих переводов и бизнес-предупреждений
- **Стек middleware** — `ForceAcceptJson`, `BindRequestContext` (request ID), `WrapApiResponse`, `AppendExecutionTimeMeta`, `ForceContentType`
- **Время выполнения** — опциональное поле `meta.execution_time` для диагностики медленных запросов
- **Artisan-команды** — `api-response:install` и `api-response:check --fix`

## Документация

> Документация находится в процессе написания. Ссылки будут обновлены перед первым стабильным релизом.

- Установка и настройка → [docs/ru/installation.md](installation.md)
- JSON-контракт ответов → `docs/ru/contract.md`
- Локализация → `docs/ru/localization.md`
- Логирование → `docs/ru/logging.md`
- Обработка исключений → `docs/ru/exceptions.md`
- Пагинация → `docs/ru/pagination.md`

## Лицензия

- Написано и защищено авторским правом &copy; 2026 [Roman Staroseltsev](https://github.com/src83).
- Программное обеспечение с открытым исходным кодом, распространяется под [лицензией MIT](../../LICENSE.md).
