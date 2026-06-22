<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Http\Responses;

use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Attributes\Test;

final class ApiErrorResponseTest extends TestCase
{
    #[Test]
    public function it_returns_error_response_with_param_set0(): void
    {
        $response = ApiErrorResponse::make(Response::HTTP_NOT_FOUND);

        $this->assertSame(404, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertSame([
            'success' => false,
            'http_code' => 404,
            'http_text' => 'Not Found',
            'message' => null,
            'details' => null,
        ], $json);
    }

    #[Test]
    public function it_returns_error_response_with_param_set1(): void
    {
        $response = ApiErrorResponse::make(Response::HTTP_UNAUTHORIZED, MessageKeyEnum::UNAUTHORIZED);

        $this->assertSame(401, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame(401, $json['http_code']);
        $this->assertSame('Unauthorized', $json['http_text']);

        $this->assertSame('unauthorized', $json['message']['key']);
        $this->assertNotEmpty($json['message']['gui']);
        $this->assertIsString($json['message']['gui']);
        $this->assertNull($json['message']['sys']);

        $this->assertNull($json['details']);
    }

    #[Test]
    public function it_returns_error_response_with_param_set2(): void
    {
        $response = ApiErrorResponse::make(
            httpCode: Response::HTTP_UNAUTHORIZED,
            messageKey: MessageKeyEnum::UNAUTHORIZED,
            sysMessage: 'Bad password',
        );

        $this->assertSame(401, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame(401, $json['http_code']);
        $this->assertSame('Unauthorized', $json['http_text']);

        $this->assertSame('unauthorized', $json['message']['key']);
        $this->assertNotEmpty($json['message']['gui']);
        $this->assertIsString($json['message']['gui']);
        $this->assertSame('Bad password', $json['message']['sys']);

        $this->assertNull($json['details']);
    }

    #[Test]
    public function it_returns_error_response_with_param_set3(): void
    {
        $response = ApiErrorResponse::make(
            httpCode: Response::HTTP_CONFLICT,
            messageKey: 'test.conflict',
            sysMessage: 'Запись заблокирована бизнес-логикой',
            details: ['uid' => 123],
        );

        $this->assertSame(409, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame(409, $json['http_code']);
        $this->assertSame('Conflict', $json['http_text']);

        $this->assertSame('test.conflict', $json['message']['key']);
        $this->assertNotEmpty($json['message']['gui']);
        $this->assertIsString($json['message']['gui']);
        $this->assertSame('Запись заблокирована бизнес-логикой', $json['message']['sys']);

        $this->assertSame(['uid' => 123], $json['details']);
    }

    #[Test]
    public function it_returns_error_response_with_sys_message_only(): void
    {
        $response = ApiErrorResponse::make(
            httpCode: 500,
            messageKey: null,
            sysMessage: 'Unexpected exception',
        );

        $this->assertSame(500, $response->getStatusCode());

        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame(500, $json['http_code']);

        $this->assertSame([
            'key' => null,
            'gui' => null,
            'sys' => 'Unexpected exception',
        ], $json['message']);
    }
}
