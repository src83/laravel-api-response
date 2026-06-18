<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Http\Responses;

use Src83\LaravelApiResponse\Http\Responses\ApiPaginatedCollectionResponse;
use Src83\LaravelApiResponse\Support\Pagination\ArrayPaginator;
use Src83\LaravelApiResponse\Tests\TestCase;

final class ApiPaginatedCollectionResponseTest extends TestCase
{
    /** @test */
    public function it_checks_data_meta_paginator_with_input_data(): void
    {
        $items = [
            ['id' => 1, 'name' => 'Test Model 01'],
            ['id' => 2, 'name' => 'Test Model 02'],
            ['id' => 3, 'name' => 'Test Model 03'],
        ];

        $paginator = ArrayPaginator::paginate(collect($items), perPage: 2, page: 1);
        $response  = ApiPaginatedCollectionResponse::fromPaginator($paginator);

        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('paginator', $json['meta']);

        $this->assertSame([
            'current_page'  => 1,
            'per_page'      => 2,
            'total_items'   => 3,
            'last_page'     => 2,
            'last_item'     => 2,
            'has_next_page' => true,
        ], $json['meta']['paginator']);

        $this->assertSame([
            ['id' => 1, 'name' => 'Test Model 01'],
            ['id' => 2, 'name' => 'Test Model 02'],
        ], $json['data']);
    }

    /** @test */
    public function it_checks_data_meta_paginator_without_input_data(): void
    {
        $paginator = ArrayPaginator::paginate(collect([]), perPage: 2, page: 1);
        $response  = ApiPaginatedCollectionResponse::fromPaginator($paginator);

        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('paginator', $json['meta']);

        $this->assertSame([
            'current_page'  => 1,
            'per_page'      => 2,
            'total_items'   => 0,
            'last_page'     => 1,
            'last_item'     => null,
            'has_next_page' => false,
        ], $json['meta']['paginator']);

        $this->assertSame([], $json['data']);
    }
}
