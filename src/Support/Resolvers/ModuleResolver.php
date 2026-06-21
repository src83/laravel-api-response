<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers;

final class ModuleResolver
{
    /**
     * Возвращает модуль:
     * 1) Если передан prefix → всегда используем его
     * 2) Иначе → если модульная система включена → берём из request()
     * 3) Иначе → null
     */
    public static function resolve(?string $prefix): ?string
    {
        if ($prefix !== null) {
            return $prefix;
        }

        if (self::isModuleEnabled()) {
            $module = self::getDefaultModule();
            $module = self::normalize($module);
            return $module;
        }

        return null;
    }

    // Проверяет включена ли модульная система API
    private static function isModuleEnabled(): bool
    {
        return config('api.is_module_available') === true;
    }

    // Извлекает модуль по умолчанию из текущего запроса (см. 'apiModule' в ApiServiceProvider)
    private static function getDefaultModule(): ?string
    {
        return request()?->apiModule();
    }

    // Поиск алиаса модуля
    private static function normalize(?string $module): ?string
    {
        if ($module === null) {
            return null;
        }

        $aliases = config('api.module_aliases', []);

        return $aliases[$module] ?? $module;
    }
}
