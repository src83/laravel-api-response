<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Helpers\Lang;

use Src83\LaravelApiResponse\Support\Resolvers\LocalizationResolver;

final class LocalizationHelper
{
    /**
     * Поиск перевода по ключу с модулем. Если модули отключены — поиск по базовому ключу.
     * Falls back to 'no_translation' if no match found.
     */
    public static function getLocalizedMessage(?string $module, string $baseKey): string
    {
        return app(LocalizationResolver::class)->getLocalizedMessage($module, $baseKey);
    }
}
