<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensures module system is active regardless of .env value
        config(['api_response.is_module_available' => true]);
    }

    // 400: BadRequest
    public function test_bad_request_exception(): void
    {
        Route::get('/api/__test/bad_request', static function () {
            throw new BadRequestException('Bad Request');
        });

        $response = $this->getJson('/api/__test/bad_request');
        $response
            ->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'http_code',
                'http_text',
                'message' => ['key', 'gui', 'sys'],
                'details',
            ])
            ->assertJson([
                'success'   => false,
                'http_code' => 400,
                'http_text' => 'Bad Request',
            ])
            ->assertJsonPath('message.key', 'test_bad_request.bad_request')
            ->assertJsonPath('message.gui', fn ($v) => is_string($v))
            ->assertJsonPath('message.sys', 'Bad Request')
            ->assertJsonPath('details', null)
        ;
    }

    // 404: NotFound
    public function test_not_found_exception(): void
    {
        Route::get('/api/__test/not_found', static function () {
            throw new NotFoundHttpException();
        });

        $response = $this->getJson('/api/__test/not_found');
        $response
            ->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'http_code',
                'http_text',
                'message' => ['key', 'gui', 'sys'],
                'details',
            ])
            ->assertJson([
                'success'   => false,
                'http_code' => 404,
                'http_text' => 'Not Found',
            ])
            ->assertJsonPath('message.key', 'test_not_found.not_found')
            ->assertJsonPath('message.gui', fn ($v) => is_string($v))
            ->assertJsonPath('message.sys', null)
            ->assertJsonPath('details', null)
        ;
    }

    // 422: Unprocessable Content — validation error
    public function test_validation_exception(): void
    {
        Route::get('/api/__test/validation', static function () {
            throw ValidationException::withMessages([
                'email' => ['Validation error'],
            ]);
        });

        $response = $this->getJson('/api/__test/validation');
        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'http_code',
                'http_text',
                'message' => ['key', 'gui', 'sys'],
                'details' => ['fields'],
            ])
            ->assertJson([
                'success'   => false,
                'http_code' => 422,
                'http_text' => 'Unprocessable Content',
            ])
            ->assertJsonPath('message.key', 'test_validation.unprocessable_content')
            ->assertJsonPath('message.gui', fn ($v) => is_string($v))
            ->assertJsonPath('message.sys', 'Validation error')
            ->assertJsonPath('details.fields', function ($fields) {
                return array_key_exists('email', $fields);
            })
        ;
    }

    // 500: Default — Internal Server Error
    public function test_internal_server_error_exception(): void
    {
        Route::get('/api/__test/internal_server_error', static function () {
            throw new \RuntimeException('Unexpected error in business logic');
        });

        $response = $this->getJson('/api/__test/internal_server_error');
        $response
            ->assertStatus(500)
            ->assertJsonStructure([
                'success',
                'http_code',
                'http_text',
                'message' => ['key', 'gui', 'sys'],
                'details',
            ])
            ->assertJson([
                'success'   => false,
                'http_code' => 500,
                'http_text' => 'Internal Server Error',
            ])
            ->assertJsonPath('message.key', 'test_internal_server_error.internal_server_error')
            ->assertJsonPath('message.gui', fn ($v) => is_string($v))
            ->assertJsonPath('message.sys', 'Unexpected error in business logic')
            ->assertJsonPath('details', function ($details) {
                return $details === null || (is_array($details) && !empty($details));
            })
        ;
    }
}
