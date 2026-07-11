<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers;

use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Helpers\Lang\LocalizationHelper;
use Src83\LaravelApiResponse\Support\Resolvers\DTO\ResolvedMessage;

final class MessageResolver
{
    public static function resolve(string|MessageKeyEnum $messageKey, ?string $guiMessage): ResolvedMessage
    {
        // 1. Приводим ключ к строке
        $messageKey = $messageKey instanceof MessageKeyEnum ? $messageKey->value : $messageKey;

        // 2. Разделяем ключ, если он составной ("module.key")
        [$prefix, $baseKey, $ignoreDefaultModule] = self::split($messageKey);

        // 3. Получаем модуль: prefix > apiModule() > null
        $module = $ignoreDefaultModule ? null : ModuleResolver::resolve($prefix);

        // 4. Формируем итоговый ключ
        $resolvedKey = $module ? $module.'.'.$baseKey : $baseKey;

        // 5. Автогенерация GUI-сообщения
        if ($guiMessage === null) {
            $guiMessage = LocalizationHelper::getLocalizedMessage($module, $baseKey);
        }

        return new ResolvedMessage(
            messageKey: $resolvedKey,
            guiMessage: $guiMessage,
            module: $module,
            baseKey: $baseKey,
        );
    }

    /**
     * Разбивает messageKey на [prefix, baseKey, ignoreDefaultModule].
     *
     * @return array{0: ?string, 1: ?string, 2: bool}
     */
    public static function split(?string $messageKey): array
    {
        if ($messageKey === null) {
            return [null, null, false];
        }

        if (!str_contains($messageKey, '.')) {
            return [null, $messageKey, false];
        }

        // Разделяем строку только по первой точке
        [$prefix, $baseKey] = explode('.', $messageKey, 2);

        $ignoreDefaultModule = false;

        // Если передан ключ с точкой вида '.created'
        if ($prefix === '') {
            $prefix = null;
            $ignoreDefaultModule = true;
        }

        $baseKey = $baseKey === '' ? null : $baseKey;

        return [$prefix, $baseKey, $ignoreDefaultModule];
    }
}
