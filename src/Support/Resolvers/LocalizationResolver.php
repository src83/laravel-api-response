<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers;

use Illuminate\Support\Facades\Lang;
use Src83\LaravelApiResponse\Support\Logging\TranslationLoggerInterface;

/**
 * @internal
 * Prefer using LocalizationHelper::getLocalizedMessage() for application-level access.
 */
final readonly class LocalizationResolver
{
    public function __construct(
        private TranslationLoggerInterface $apiLogger,
    ) {}

    public function getLocalizedMessage(?string $module, string $baseKey): string
    {
        $locale   = app()->getLocale();
        $strategy = config('api.translation_lookup', 'strict');

        if (!empty($module)) {
            $moduleKey = "api_results.$module.$baseKey";

            if ($strategy === 'strict' && Lang::has($moduleKey, $locale, false)) {
                return Lang::get($moduleKey, [], $locale);
            }

            if ($strategy === 'graceful') {
                $translation = __($moduleKey, locale: $locale);
                if ($translation !== $moduleKey) {
                    return $translation;
                }
            }

            $this->apiLogger->translationMissing([
                'locale'   => $locale,
                'module'   => $module,
                'key'      => $baseKey,
                'level'    => 'module',
                'strategy' => $strategy,
            ]);
        }

        $baseKeyFull = "api_results.$baseKey";

        if ($strategy === 'strict' && Lang::has($baseKeyFull, $locale, false)) {
            return Lang::get($baseKeyFull, [], $locale);
        }

        if ($strategy === 'graceful') {
            $translation = __($baseKeyFull, locale: $locale);
            if ($translation !== $baseKeyFull) {
                return $translation;
            }
        }

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
