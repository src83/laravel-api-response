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
            return self::normalize($module);
        }

        return null;
    }

    private static function isModuleEnabled(): bool
    {
        return config('api.is_module_available') === true;
    }

    private static function getDefaultModule(): ?string
    {
        return request()?->apiModule();
    }

    private static function normalize(?string $module): ?string
    {
        if ($module === null) {
            return null;
        }

        $aliases = config('api.module_aliases', []);

        return $aliases[$module] ?? $module;
    }
}
