<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Helpers\Lang;

use Illuminate\Support\Facades\Lang;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Src83\LaravelApiResponse\Support\Logging\ApiLoggerInterface;
use Src83\LaravelApiResponse\Support\Resolvers\LocalizationResolver;
use Src83\LaravelApiResponse\Tests\TestCase;

final class LocalizationHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function t1_module_and_base_key_found_translation_by_both_no_log(): void
    {
        Lang::addLines([
            'api_response.test.unprocessable_content' => 'Validation error [module: test]',
            'api_response.unprocessable_content'      => 'Validation error',
        ], 'en');

        $module = 'test';
        $baseKey = 'unprocessable_content';

        $loggerMock = Mockery::mock(ApiLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->never();  // When a translation is found — no log entry should be created
        $this->app->instance(ApiLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('Validation error [module: test]', $guiMessage);
    }

    #[Test]
    public function t2_module_and_base_key_found_translation_just_by_base_key_log_1_time(): void
    {
        Lang::addLines([
            'api_response.unprocessable_content' => 'Validation error',
        ], 'en');

        $module = 'test';
        $baseKey = 'unprocessable_content';

        $loggerMock = Mockery::mock(ApiLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->with(Mockery::type('array'))
            ->once();  // When the module translation is not found — log just once
        $this->app->instance(ApiLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('Validation error', $guiMessage);
    }

    #[Test]
    public function t3_module_and_base_key_not_found_translation_log_2_times(): void
    {
        Lang::addLines([
            'api_response.test.unprocessable_content' => 'Validation error [module: test]',
            'api_response.unprocessable_content'      => 'Validation error',
        ], 'en');

        $module = 'test';
        $baseKey = 'unknown_key';

        $loggerMock = Mockery::mock(ApiLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->with(Mockery::type('array'))
            ->twice();  // When neither the module nor the base translation is found — log twice
        $this->app->instance(ApiLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('no_translation', $guiMessage);
    }

    #[Test]
    public function t4_base_key_found_translation_by_base_key_no_log(): void
    {
        Lang::addLines([
            'api_response.unprocessable_content' => 'Validation error',
        ], 'en');

        $module = null;
        $baseKey = 'unprocessable_content';

        $loggerMock = Mockery::mock(ApiLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->never();  // When a translation is found — no log entry should be created
        $this->app->instance(ApiLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('Validation error', $guiMessage);
    }

    #[Test]
    public function t5_base_key_not_found_translation_log_1_time(): void
    {
        Lang::addLines([
            'api_response.unprocessable_content' => 'Validation error',
        ], 'en');

        $module = null;
        $baseKey = 'unknown_key';

        $loggerMock = Mockery::mock(ApiLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->with(Mockery::type('array'))
            ->once();  // When the base translation is not found — log just once
        $this->app->instance(ApiLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('no_translation', $guiMessage);
    }
}
