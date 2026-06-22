<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Http\Responses;

use InvalidArgumentException;
use Src83\LaravelApiResponse\Http\Responses\ApiResponse;
use Src83\LaravelApiResponse\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ApiResponseTest extends TestCase
{
    #[Test]
    public function it_returns_success_response_with_default_structure(): void
    {
        $response = ApiResponse::success();

        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success'   => true,
            'http_code' => 200,
            'http_text' => 'OK',
            'message'   => null,
            'meta'      => null,
            'data'      => null,
        ], $json);
    }

    #[Test]
    public function it_returns_success_response_with_data(): void
    {
        $data = ['id' => 1];

        $response = ApiResponse::success($data);

        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success'   => true,
            'http_code' => 200,
            'http_text' => 'OK',
            'message'   => null,
            'meta'      => null,
            'data'      => ['id' => 1],
        ], $json);
    }

    #[Test]
    public function it_returns_success_response_includes_message_container_with_data(): void
    {
        $response = ApiResponse::success(
            data:       ['id' => 2],
            httpCode:   201,
            messageKey: 'auth.created',
            guiMessage: 'User created',
        );

        $json = $response->getData(true);

        $this->assertSame([
            'success'   => true,
            'http_code' => 201,
            'http_text' => 'Created',
            'message'   => [
                'key' => 'auth.created',
                'gui' => 'User created',
            ],
            'meta'      => null,
            'data'      => ['id' => 2],
        ], $json);
    }

    #[Test]
    public function it_returns_error_response_with_default_structure(): void
    {
        $response = ApiResponse::error(404);

        $this->assertSame(404, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success'   => false,
            'http_code' => 404,
            'http_text' => 'Not Found',
            'message'   => null,
            'details'   => null,
        ], $json);
    }

    #[Test]
    public function it_includes_message_and_details_when_provided(): void
    {
        $response = ApiResponse::error(
            httpCode:   422,
            messageKey: 'validation.failed',
            guiMessage: 'Validation error',
            sysMessage: 'Invalid payload',
            details:    ['email' => ['Required']],
        );

        $json = $response->getData(true);

        $this->assertSame([
            'success'   => false,
            'http_code' => 422,
            'http_text' => 'Unprocessable Content',
            'message'   => [
                'key' => 'validation.failed',
                'gui' => 'Validation error',
                'sys' => 'Invalid payload',
            ],
            'details'   => ['email' => ['Required']],
        ], $json);
    }

    #[Test]
    public function it_throws_exception_for_unknown_http_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown HTTP code');

        ApiResponse::success(null, null, 999);
    }
}
