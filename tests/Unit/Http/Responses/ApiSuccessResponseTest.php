<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Http\Responses;

use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Http\Responses\ApiSuccessResponse;
use Src83\LaravelApiResponse\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class ApiSuccessResponseTest extends TestCase
{
    /** @test */
    public function it_returns_success_response_with_param_set0(): void
    {
        $response = ApiSuccessResponse::make();

        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success' => true,
            'http_code' => 200,
            'http_text' => 'OK',
            'message' => null,
            'meta' => null,
            'data' => null,
        ], $json);
    }

    /** @test */
    public function it_returns_success_response_with_param_set1(): void
    {
        $response = ApiSuccessResponse::make(['id' => 1]);

        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success' => true,
            'http_code' => 200,
            'http_text' => 'OK',
            'message' => null,
            'meta' => null,
            'data' => ['id' => 1],
        ], $json);
    }

    /** @test */
    public function it_returns_success_response_with_param_set2(): void
    {
        $response = ApiSuccessResponse::make(
            data: ['id' => 1],
            httpCode: Response::HTTP_CREATED,
        );

        $this->assertSame(201, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success' => true,
            'http_code' => 201,
            'http_text' => 'Created',
            'message' => null,
            'meta' => null,
            'data' => ['id' => 1],
        ], $json);
    }

    /** @test */
    public function it_returns_success_response_with_param_set3(): void
    {
        $response = ApiSuccessResponse::make(
            data:       ['id' => 1],
            httpCode:   Response::HTTP_CREATED,
            messageKey: MessageKeyEnum::CREATED,
        );

        $this->assertSame(201, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertTrue($json['success']);
        $this->assertSame(201, $json['http_code']);
        $this->assertSame('Created', $json['http_text']);

        $this->assertSame('created', $json['message']['key']);
        $this->assertNotEmpty($json['message']['gui']);
        $this->assertIsString($json['message']['gui']);

        $this->assertSame(['id' => 1], $json['data']);
    }

    /** @test */
    public function it_returns_success_response_with_param_set4(): void
    {
        $response = ApiSuccessResponse::make(
            data:       ['id' => 1],
            httpCode:   Response::HTTP_CREATED,
            messageKey: MessageKeyEnum::CREATED,
            guiMessage: 'Created successfully',
        );

        $this->assertSame([
            'success'   => true,
            'http_code' => 201,
            'http_text' => 'Created',
            'message'   => ['key' => 'created', 'gui' => 'Created successfully'],
            'meta'      => null,
            'data'      => ['id' => 1],
        ], $response->getData(true));
    }

    /** @test */
    public function it_returns_success_response_with_param_set5(): void
    {
        $response = ApiSuccessResponse::make(
            data:       ['id' => 1],
            httpCode:   Response::HTTP_CREATED,
            messageKey: null,
            guiMessage: 'Created successfully',
        );

        $this->assertSame([
            'success'   => true,
            'http_code' => 201,
            'http_text' => 'Created',
            'message'   => ['key' => null, 'gui' => 'Created successfully'],
            'meta'      => null,
            'data'      => ['id' => 1],
        ], $response->getData(true));
    }

    /** @test */
    public function it_returns_success_response_with_param_set6(): void
    {
        $response = ApiSuccessResponse::make(messageKey: MessageKeyEnum::UPDATED);

        $json = $response->getData(true);

        $this->assertTrue($json['success']);
        $this->assertSame(200, $json['http_code']);
        $this->assertSame('OK', $json['http_text']);
        $this->assertSame('updated', $json['message']['key']);
        $this->assertNotEmpty($json['message']['gui']);
        $this->assertIsString($json['message']['gui']);
        $this->assertNull($json['data']);
    }

    /** @test */
    public function it_returns_success_response_with_param_set7(): void
    {
        $response = ApiSuccessResponse::make(
            messageKey: MessageKeyEnum::UPDATED,
            guiMessage: 'Model updated successfully',
        );

        $this->assertSame([
            'success'   => true,
            'http_code' => 200,
            'http_text' => 'OK',
            'message'   => ['key' => 'updated', 'gui' => 'Model updated successfully'],
            'meta'      => null,
            'data'      => null,
        ], $response->getData(true));
    }

    /** @test */
    public function it_returns_success_response_with_param_set8(): void
    {
        $response = ApiSuccessResponse::make(guiMessage: 'Model updated successfully');

        $this->assertSame([
            'success'   => true,
            'http_code' => 200,
            'http_text' => 'OK',
            'message'   => ['key' => null, 'gui' => 'Model updated successfully'],
            'meta'      => null,
            'data'      => null,
        ], $response->getData(true));
    }
}
