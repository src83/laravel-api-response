<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Http\Responses;

use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class ApiErrorResponseTest extends TestCase
{
    /** @test */
    public function it_returns_error_response_with_param_set0(): void
    {
        $response = ApiErrorResponse::make(Response::HTTP_NOT_FOUND);

        $this->assertSame(404, $response->getStatusCode());

        $this->assertSame([
            'success'   => false,
            'http_code' => 404,
            'http_text' => 'Not Found',
            'message'   => null,
            'details'   => null,
        ], $response->getData(true));
    }

    /** @test */
    public function it_returns_error_response_with_param_set1(): void
    {
        $response = ApiErrorResponse::make(Response::HTTP_UNAUTHORIZED, MessageKeyEnum::UNAUTHORIZED);

        $json = $response->getData(true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertFalse($json['success']);
        $this->assertSame('unauthorized', $json['message']['key']);
        $this->assertNotEmpty($json['message']['gui']);
        $this->assertNull($json['message']['sys']);
        $this->assertNull($json['details']);
    }

    /** @test */
    public function it_returns_error_response_with_param_set2(): void
    {
        $response = ApiErrorResponse::make(
            httpCode:   Response::HTTP_UNAUTHORIZED,
            messageKey: MessageKeyEnum::UNAUTHORIZED,
            sysMessage: 'Bad password',
        );

        $json = $response->getData(true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('unauthorized', $json['message']['key']);
        $this->assertSame('Bad password', $json['message']['sys']);
        $this->assertNull($json['details']);
    }

    /** @test */
    public function it_returns_error_response_with_param_set3(): void
    {
        $response = ApiErrorResponse::make(
            httpCode:   Response::HTTP_CONFLICT,
            messageKey: 'test.conflict',
            sysMessage: 'Запись заблокирована бизнес-логикой',
            details:    ['uid' => 123],
        );

        $json = $response->getData(true);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertFalse($json['success']);
        $this->assertSame('test.conflict', $json['message']['key']);
        $this->assertSame('Запись заблокирована бизнес-логикой', $json['message']['sys']);
        $this->assertSame(['uid' => 123], $json['details']);
    }

    /** @test */
    public function it_returns_error_response_with_sys_message_only(): void
    {
        $response = ApiErrorResponse::make(
            httpCode:   500,
            messageKey: null,
            sysMessage: 'Unexpected exception',
        );

        $this->assertSame(500, $response->getStatusCode());

        $this->assertSame([
            'key' => null,
            'gui' => null,
            'sys' => 'Unexpected exception',
        ], $response->getData(true)['message']);
    }
}
