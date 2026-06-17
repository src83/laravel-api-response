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
        $messageKey = $messageKey instanceof MessageKeyEnum ? $messageKey->value : $messageKey;

        [$prefix, $baseKey, $ignoreDefaultModule] = self::split($messageKey);

        $module = $ignoreDefaultModule ? null : ModuleResolver::resolve($prefix);

        $resolvedKey = $module ? $module . '.' . $baseKey : $baseKey;

        if ($guiMessage === null) {
            $guiMessage = LocalizationHelper::getLocalizedMessage($module, $baseKey);
        }

        return new ResolvedMessage(
            messageKey: $resolvedKey,
            guiMessage: $guiMessage,
            module:     $module,
            baseKey:    $baseKey,
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

        [$prefix, $baseKey] = explode('.', $messageKey, 2);

        $ignoreDefaultModule = false;

        if ($prefix === '') {
            $prefix              = null;
            $ignoreDefaultModule = true;
        }

        $baseKey = $baseKey === '' ? null : $baseKey;

        return [$prefix, $baseKey, $ignoreDefaultModule];
    }
}
