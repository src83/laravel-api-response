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
        // 1. Cast the key to a string
        $messageKey = $messageKey instanceof MessageKeyEnum ? $messageKey->value : $messageKey;

        // 2. Split the key if it's composite ("module.key")
        [$prefix, $baseKey, $ignoreDefaultModule] = self::split($messageKey);

        // 3. Resolve the module: prefix > apiModule() > null
        $module = $ignoreDefaultModule ? null : ModuleResolver::resolve($prefix);

        // 4. Build the final key
        $resolvedKey = $module ? $module.'.'.$baseKey : $baseKey;

        // 5. Auto-generate the GUI message
        if ($guiMessage === null) {
            $guiMessage = LocalizationHelper::getLocalizedMessage($module, $baseKey ?? '');
        }

        return new ResolvedMessage(
            messageKey: $resolvedKey ?? '',
            guiMessage: $guiMessage,
            module: $module,
            baseKey: $baseKey,
        );
    }

    /**
     * Splits messageKey into [prefix, baseKey, ignoreDefaultModule].
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

        // Split the string on the first dot only
        [$prefix, $baseKey] = explode('.', $messageKey, 2);

        $ignoreDefaultModule = false;

        // Leading dot (e.g. '.created') means: skip auto-module, use base key only
        if ($prefix === '') {
            $prefix = null;
            $ignoreDefaultModule = true;
        }

        $baseKey = $baseKey === '' ? null : $baseKey;

        return [$prefix, $baseKey, $ignoreDefaultModule];
    }
}
