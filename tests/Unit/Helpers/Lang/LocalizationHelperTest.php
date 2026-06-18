<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Helpers\Lang;

use Illuminate\Support\Facades\Lang;
use Mockery;
use Src83\LaravelApiResponse\Support\Logging\TranslationLoggerInterface;
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

    /** @test */
    public function T1_module_and_baseKey_found_translation_by_both_no_log(): void
    {
        Lang::addLines([
            'api_results.test.unprocessable_content' => 'Validation error [module: test]',
            'api_results.unprocessable_content'      => 'Validation error',
        ], 'en');

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock->expects('translationMissing')->never();
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver   = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage('test', 'unprocessable_content');

        $this->assertSame('Validation error [module: test]', $guiMessage);
    }

    /** @test */
    public function T2_module_and_baseKey_found_translation_just_by_baseKey_log_1_time(): void
    {
        Lang::addLines([
            'api_results.unprocessable_content' => 'Validation error',
        ], 'en');

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock->expects('translationMissing')->with(Mockery::type('array'))->once();
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver   = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage('test', 'unprocessable_content');

        $this->assertSame('Validation error', $guiMessage);
    }

    /** @test */
    public function T3_module_and_baseKey_not_found_translation_log_2_times(): void
    {
        Lang::addLines([
            'api_results.test.unprocessable_content' => 'Validation error [module: test]',
            'api_results.unprocessable_content'      => 'Validation error',
        ], 'en');

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock->expects('translationMissing')->with(Mockery::type('array'))->twice();
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver   = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage('test', 'unknown_key');

        $this->assertSame('no_translation', $guiMessage);
    }

    /** @test */
    public function T4_baseKey_found_translation_by_baseKey_no_log(): void
    {
        Lang::addLines([
            'api_results.unprocessable_content' => 'Validation error',
        ], 'en');

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock->expects('translationMissing')->never();
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver   = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage(null, 'unprocessable_content');

        $this->assertSame('Validation error', $guiMessage);
    }

    /** @test */
    public function T5_baseKey_not_found_translation_log_1_time(): void
    {
        Lang::addLines([
            'api_results.unprocessable_content' => 'Validation error',
        ], 'en');

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock->expects('translationMissing')->with(Mockery::type('array'))->once();
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver   = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage(null, 'unknown_key');

        $this->assertSame('no_translation', $guiMessage);
    }
}
