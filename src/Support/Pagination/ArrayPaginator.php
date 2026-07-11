<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

final class ArrayPaginator
{
    public static function paginate(iterable $items, int $perPage, int $page): LengthAwarePaginator
    {
        $collection = collect($items);

        return new LengthAwarePaginator(
            items: $collection->forPage($page, $perPage)->values(),
            total: $collection->count(),
            perPage: $perPage,
            currentPage: $page,
        );
    }
}
