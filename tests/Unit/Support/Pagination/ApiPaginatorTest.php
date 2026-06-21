<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;
use Src83\LaravelApiResponse\Tests\TestCase;

final class ApiPaginatorTest extends TestCase
{
    public function test_it_creates_api_paginator_from_length_aware_paginator(): void
    {
        // Arrange
        $items = collect([
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ]);

        $laravelPaginator = new LengthAwarePaginator(
            items: $items,
            total: 9,
            perPage: 3,
            currentPage: 2,
        );

        // Act
        $apiPaginator = ApiPaginator::from($laravelPaginator);

        // Assert (DTO values)
        $this->assertSame(2, $apiPaginator->currentPage);
        $this->assertSame(3, $apiPaginator->perPage);
        $this->assertSame(9, $apiPaginator->totalItems);
        $this->assertSame(3, $apiPaginator->lastPage);
        $this->assertSame(6, $apiPaginator->lastItem);
        $this->assertTrue($apiPaginator->hasNextPage);
    }

    public function test_it_serializes_to_array_correctly(): void
    {
        // Arrange
        $paginator = new ApiPaginator(
            currentPage: 1,
            perPage: 10,
            totalItems: 15,
            lastPage: 2,
            lastItem: 10,
            hasNextPage: true,
        );

        // Act
        $array = $paginator->toArray();

        // Assert
        $this->assertSame([
            'current_page'  => 1,
            'per_page'      => 10,
            'total_items'   => 15,
            'last_page'     => 2,
            'last_item'     => 10,
            'has_next_page' => true,
        ], $array);
    }
}
