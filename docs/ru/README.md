# Unified REST API Response formatter for Laravel

`LaravelApiResponse` — это [Laravel](https://laravel.com/)-пакет для формирования консистентных, хорошо структурированных REST API JSON ответов.
Включает автоматическую обработку исключений, модульную локализацию сообщений, структурированное логирование и пагинацию из коробки.

[![Tests](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/tests.yml)
[![Code Style](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/pint.yml)
[![Static Analysis](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml/badge.svg)](https://github.com/src83/laravel-api-response/actions/workflows/phpstan.yml)
[![Latest Stable Version](https://poser.pugx.org/src83/laravel-api-response/v)](https://packagist.org/packages/src83/laravel-api-response)
[![License](https://poser.pugx.org/src83/laravel-api-response/license)](../../LICENSE.md)

**Доступно на других языках:** [EN](../../README.md)

---

## Содержание

- [Введение](#введение)
- [Требования](#требования)
- [Установка](#установка)
- [Быстрый старт](#быстрый-старт)
- [Возможности](#возможности)
- [Принципы проектирования](#принципы-проектирования)
- [Документация](#документация)
- [История изменений](../../CHANGELOG.md)
- [Лицензия](#лицензия)

---

## Введение

Каждый разработчик Laravel API сталкивался с одной и той же проблемой: возникает исключение — и вместо JSON клиент получает тонну HTML. `laravel-api-response` решает эту проблему — и заодно обеспечивает единую, предсказуемую структуру для всех ответов, на которую всегда может опираться ваш фронтенд.

Каждый ответ содержит структурированный объект `message` с машиночитаемым `key` и человекочитаемой строкой `gui`, которая формируется из ваших файлов переводов. Ошибки дополнительно включают поле `sys` для внутреннего контекста.

**Пример успешного ответа:**

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
    "data": {
        "id": 42,
        "email": "user@example.com"
    }
}
```

**Пример ответа с ошибкой** (в том числе исключений — всегда JSON, никогда HTML):

```json
{
    "success": false,
    "http_code": 422,
    "http_text": "Unprocessable Content",
    "message": {
        "key": "user.unprocessable_content",
        "gui": "Ошибка валидации",
        "sys": "Требуется указать поле Email"
    },
    "details": {
        "fields": {
            "email": ["Поле email обязательно для заполнения."]
        }
    }
}
```

Поля `data` и `details` являются взаимоисключающими — `data` и `meta` присутствуют в **успешных** ответах, `details` — в ответах с **ошибками**. `meta` равно `null` для простых ответов и содержит данные пагинации, если ответ пагинирован.

## Требования

|         | Версии       |
|---------|--------------|
| PHP     | 8.2 и выше   |
| Laravel | 9, 10 или 11 |

## Установка

**1. Добавьте пакет в зависимости:**

```bash
composer require src83/laravel-api-response
```

**2. Запустите установщик:**

```bash
php artisan api-response:install
```
Установщик добавляет одни элементы и изменяет другие в вашем существующем проекте. Вы можете просмотреть эти точечные изменения, выполнив `git status` после запуска команды установки.

Особое внимание стоит уделить `Handler.php` и `Authenticate.php`.

Этот пакет можно установить как на свежее приложение Laravel, так и на уже существующий проект с большим количеством бизнес-логики и кастомизированным обработчиком исключений.

В зависимости от стадии проекта установщик может вести себя по-разному (адаптивно):

* В новом, чистом Laravel-проекте эти два файла при установке будут полностью обновлены, но, тем не менее, требуют минимального ревью для принятия изменений. После завершения установки вы можете сразу перейти к следующему шагу — "Проверка установки", чтобы убедиться, что пакет установлен корректно.

* При установке пакета на более зрелый проект файлы `Handler.php` и `Authenticate.php` могут не обновиться сразу, если установщик обнаружит, что в них уже присутствует кастомизированная логика. Такие элементы будут отмечены как `ACTION REQUIRED`. Это штатная ситуация. В любом случае переходите к следующему шагу — "Проверка установки".

**3. Проверка установки:**

```bash
php artisan api-response:check
```

Подтверждает, что все компоненты установлены корректно.  
Если что-то отмечено как `ACTION REQUIRED` — это значит, что установщик обнаружил в них кастомную логику и не стал перезаписывать их автоматически.

В этом случае рекомендуется:
- Сделать бекап файлов с пометкой `ACTION REQUIRED`;
- Принудительно обновить указанные файлы, выполнив команду:
    ```bash
    php artisan api-response:check --fix
    ```
- Вручную разрешить конфликты слияния текущей и новой бизнес-логики в обновлённых файлах.

Всё, пакет установлен и готов к работе.  
Можно теперь ещё раз, на всякий случай, снова выполнить `php artisan api-response:check` и переходить к самому интересному — разработке.

## Быстрый старт

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

Полный справочник — сигнатуры вызовов, структура ответов и пагинация — см. в [docs/api-contract.md](docs/api-contract.md)

## Возможности

- **Единый JSON-контракт** — все ответы, включая исключения, возвращают одну и ту же структуру
- **Исключения → JSON** — HTTP и доменные исключения перехватываются и возвращаются как структурированные ошибки, никакого HTML
- **Модульная локализация** — `gui`-сообщения разрешаются из `lang/{locale}/api_response.php` по модулю и ключу
- **Пагинация** — встроенный `ApiPaginator` для Eloquent-пагинаторов и `ArrayPaginator` для обычных массивов
- **Структурированное логирование** — отдельные каналы для throwable-ошибок, rendered-ошибок, отсутствующих переводов и бизнес-предупреждений
- **Стек middleware** — `ForceAcceptJson`, `BindRequestContext` (request ID), `WrapApiResponse`, `AppendExecutionTimeMeta`, `ForceContentType`
- **Время выполнения** — опциональное поле `meta.execution_time` для диагностики медленных запросов
- **Artisan-команды** — `api-response:install` и `api-response:check [--fix]`
- **Встроенные исключения** — `DomainLayerException` и `ItemNotFoundException` готовы к использованию в доменном слое
- **Request-макросы** — `$request->isApi()` и `$request->apiModule()` доступны из коробки
- **Стандартный словарь действий** — `MessageKeyEnum` покрывает все типовые CRUD-ключи и статусы

## Принципы проектирования

- Контроллеры не формируют сообщения — они передают `messageKey`, пакет разрешает перевод
- `messageKey` может быть значением `MessageKeyEnum`, строкой или составным ключом вида `module.key`
- Модуль определяется автоматически из префикса маршрута — никакой ручной настройки для каждого эндпоинта
- Все исключения возвращают единый JSON — никаких исключений, никакого HTML

## Документация

> Основное описано в этом README. Подробная документация в процессе написания — за деталями смотрите комментарии в 
> исходном коде или [задайте вопрос через issues](https://github.com/src83/laravel-api-response/issues).

- Установка → [docs/ru/installation.md](installation.md)
- API-справочник (контракт + примеры вызовов) → [docs/ru/api-contract.md](api-contract.md)
- Справочник конфигурации → [docs/ru/configuration.md](configuration.md)
- Локализация → [docs/ru/localization.md](localization.md)
- Логирование → [docs/ru/logging.md](logging.md)
- Обработка исключений → [docs/ru/exceptions.md](exceptions.md)

## Лицензия

- Разработано и защищено авторским правом &copy; 2026 [Roman Staroseltsev](https://github.com/src83)
- Программное обеспечение с открытым исходным кодом, распространяется под [лицензией MIT](../../LICENSE.md)
- Автор: [LinkedIn](https://linkedin.com/in/staroseltsev)
