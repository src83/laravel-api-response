<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;
use Src83\LaravelApiResponse\Support\Pagination\ArrayPaginator;
use Src83\LaravelApiResponse\Tests\TestCase;

final class ArrayPaginatorTest extends TestCase
{
    public function test_it_paginates_array_correctly(): void
    {
        // Arrange
        $items = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ];

        // Act
        $paginator = ArrayPaginator::paginate(
            items: $items,
            perPage: 2,
            page: 2,
        );

        // Assert: paginator meta
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame(5, $paginator->total());
        $this->assertSame(2, $paginator->perPage());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame(3, $paginator->lastPage());

        // Assert: items on page
        $this->assertSame(
            [
                ['id' => 3],
                ['id' => 4],
            ],
            $paginator->items()
        );
    }

    public function test_it_returns_empty_items_when_page_out_of_range(): void
    {
        // Arrange
        $items = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];

        // Act
        $paginator = ArrayPaginator::paginate(
            items: $items,
            perPage: 2,
            page: 5,
        );

        // Assert
        $this->assertSame(3, $paginator->total());
        $this->assertSame(5, $paginator->currentPage());
        $this->assertSame([], $paginator->items());
        $this->assertFalse($paginator->hasMorePages());
    }
}
