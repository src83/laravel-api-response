<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ApiPaginator
{
    public function __construct(
        public int  $page,
        public int  $perPage,
        public int  $totalItems,
        public int  $totalPages,
        public ?int $lastItem,
        public bool $hasNextPage,
    ) {}

    public static function from(LengthAwarePaginator $p): self
    {
        return new self(
            page:        $p->currentPage(),
            perPage:     $p->perPage(),
            totalItems:  $p->total(),
            totalPages:  $p->lastPage(),
            lastItem:    $p->lastItem() ?? 0,
            hasNextPage: $p->hasMorePages(),
        );
    }

    public function toArray(): array
    {
        return [
            'page'          => $this->page,
            'per_page'      => $this->perPage,
            'total_items'   => $this->totalItems,
            'total_pages'   => $this->totalPages,
            'last_item'     => $this->lastItem,
            'has_next_page' => $this->hasNextPage,
        ];
    }
}
