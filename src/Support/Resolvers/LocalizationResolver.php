<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers;

use Illuminate\Support\Facades\Lang;
use Src83\LaravelApiResponse\Support\Logging\ApiLoggerInterface;

/**
 * @internal
 * Internal localization resolver.
 *
 * This class is resolved via DI — don't access it directly through the app container; use LocalizationHelper instead.
 * Prefer using LocalizationHelper::getLocalizedMessage() for application-level access.
 */
final readonly class LocalizationResolver
{
    public function __construct(
        private ApiLoggerInterface $apiLogger,
    ) {}

    public function getLocalizedMessage(?string $module, string $baseKey): string
    {
        $locale = app()->getLocale();
        $strategy = config('api_response.translation_lookup', 'strict');

        // 1. Try to find a translation by the composite key
        if (!empty($module)) {
            $moduleKey = "api_response.$module.$baseKey";

            if ($strategy === 'strict' && Lang::has($moduleKey, $locale, false)) {
                return Lang::get($moduleKey, [], $locale);
            }

            if ($strategy === 'graceful') {
                $translation = __($moduleKey, locale: $locale);
                if ($translation !== $moduleKey) {
                    return $translation;
                }
            }

            // If no translation is found for the module key — log it
            $this->apiLogger->translationMissing([
                'locale'   => $locale,
                'module'   => $module,
                'key'      => $baseKey,
                'level'    => 'module',
                'strategy' => $strategy,
            ]);
        }

        // 2. Try to find a translation using only the base key
        $baseKeyFull = "api_response.$baseKey";

        if ($strategy === 'strict' && Lang::has($baseKeyFull, $locale, false)) {
            return Lang::get($baseKeyFull, [], $locale);
        }

        if ($strategy === 'graceful') {
            $translation = __($baseKeyFull, locale: $locale);
            if ($translation !== $baseKeyFull) {
                return $translation;
            }
        }

        // If no translation found for the base key (without module) — log it
        $this->apiLogger->translationMissing([
            'locale'   => $locale,
            'module'   => null,
            'key'      => $baseKey,
            'level'    => 'base',
            'strategy' => $strategy,
        ]);

        return 'no_translation';
    }
}
