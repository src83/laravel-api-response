<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Pagination;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ApiPaginator
{
    public function __construct(
        public int $currentPage,
        public int $perPage,
        public int $totalItems,
        public int $lastPage,
        public ?int $lastItem,
        public bool $hasNextPage,
    ) {}

    public static function from(LengthAwarePaginator $p): self
    {
        return new self(
            currentPage: $p->currentPage(),
            perPage: $p->perPage(),
            totalItems: $p->total(),
            lastPage: $p->lastPage(),
            lastItem: $p->lastItem(),
            hasNextPage: $p->hasMorePages(),
        );
    }

    public function toArray(): array
    {
        return [
            'current_page'  => $this->currentPage,
            'per_page'      => $this->perPage,
            'total_items'   => $this->totalItems,
            'last_page'     => $this->lastPage,
            'last_item'     => $this->lastItem,
            'has_next_page' => $this->hasNextPage,
        ];
    }
}
