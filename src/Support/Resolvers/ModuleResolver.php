<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers;

final class ModuleResolver
{
    /**
     * Resolves the module name using the following priority:
     * 1) If $prefix has been passed — always use it
     * 2) Otherwise, if the module system is enabled — take it from the current request
     * 3) Otherwise — null
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

    // Checks whether the modular API system is enabled
    private static function isModuleEnabled(): bool
    {
        return config('api_response.is_module_available') === true;
    }

    // Resolves the default module from the current request (see 'apiModule' in ApiServiceProvider)
    private static function getDefaultModule(): ?string
    {
        return request()->apiModule();
    }

    // Looks up the module alias
    private static function normalize(?string $module): ?string
    {
        if ($module === null) {
            return null;
        }

        /** @var array<string, string> $aliases */
        $aliases = (array) config('api_response.module_aliases', []);

        return $aliases[$module] ?? $module;
    }
}
