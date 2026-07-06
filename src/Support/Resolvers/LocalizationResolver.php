<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers;

use Illuminate\Support\Facades\Lang;
use Src83\LaravelApiResponse\Support\Logging\TranslationLoggerInterface;

/**
 * @internal
 * Internal localization resolver.
 *
 * Класс резолвится через DI, напрямую из приложения его брать не надо, есть LocalizationHelper
 * Prefer using LocalizationHelper::getLocalizedMessage() for application-level access.
 */
final readonly class LocalizationResolver
{
    public function __construct(
        private TranslationLoggerInterface $apiLogger,
    ) {}

    public function getLocalizedMessage(?string $module, string $baseKey): string
    {
        $locale = app()->getLocale();
        $strategy = config('api.translation_lookup', 'strict');

        // 1. Пробуем найти перевод по составному ключу
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

            // Перевод для модульного ключа отсутствует — логируем
            $this->apiLogger->translationMissing([
                'locale'   => $locale,
                'module'   => $module,
                'key'      => $baseKey,
                'level'    => 'module',
                'strategy' => $strategy,
            ]);
        }

        // 2. Пробуем найти перевод только по базовому ключу
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

        // Перевод для базового ключа (без модуля) отсутствует — логируем
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
