<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Helpers\Data;

final class StringHelper
{
    public static function titleToSnakeCase(string $value): string
    {
        return strtolower(str_replace(' ', '_', $value));
    }

    public static function snakeToTitleCase(string $value): string
    {
        return ucfirst(str_replace('_', ' ', $value));
    }

    public static function truncate(string $text, int $limit = 100): string
    {
        return mb_strimwidth($text, 0, $limit, '...');
    }
}
