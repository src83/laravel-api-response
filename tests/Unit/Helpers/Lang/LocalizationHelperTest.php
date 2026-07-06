<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Helpers\Lang;

use Illuminate\Support\Facades\Lang;
use Mockery;
use Src83\LaravelApiResponse\Support\Logging\TranslationLoggerInterface;
use Src83\LaravelApiResponse\Support\Resolvers\LocalizationResolver;
use Src83\LaravelApiResponse\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
    public function T1_module_and_baseKey_found_translation_by_both_no_log(): void
    {
        Lang::addLines([
            'api_response.test.unprocessable_content' => 'Validation error [module: test]',
            'api_response.unprocessable_content'      => 'Validation error',
        ], 'en');

        $module  = 'test';
        $baseKey = 'unprocessable_content';

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->never();  // Когда перевод найден -- лога быть не может
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('Validation error [module: test]', $guiMessage);
    }

    #[Test]
    public function T2_module_and_baseKey_found_translation_just_by_baseKey_log_1_time(): void
    {
        Lang::addLines([
            'api_response.unprocessable_content' => 'Validation error',
        ], 'en');

        $module  = 'test';
        $baseKey = 'unprocessable_content';

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->with(Mockery::type('array'))
            ->once();  // Когда модульный перевод не найден -- логируем один раз
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('Validation error', $guiMessage);
    }

    #[Test]
    public function T3_module_and_baseKey_not_found_translation_log_2_times(): void
    {
        Lang::addLines([
            'api_response.test.unprocessable_content' => 'Validation error [module: test]',
            'api_response.unprocessable_content'      => 'Validation error',
        ], 'en');

        $module  = 'test';
        $baseKey = 'unknown_key';

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->with(Mockery::type('array'))
            ->twice();  // Когда и модульный и базовый перевод не найден -- логируем два раза
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('no_translation', $guiMessage);
    }

    #[Test]
    public function T4_baseKey_found_translation_by_baseKey_no_log(): void
    {
        Lang::addLines([
            'api_response.unprocessable_content' => 'Validation error',
        ], 'en');

        $module  = null;
        $baseKey = 'unprocessable_content';

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->never();  // Когда перевод найден -- лога быть не может
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('Validation error', $guiMessage);
    }

    #[Test]
    public function T5_baseKey_not_found_translation_log_1_time(): void
    {
        Lang::addLines([
            'api_response.unprocessable_content' => 'Validation error',
        ], 'en');

        $module  = null;
        $baseKey = 'unknown_key';

        $loggerMock = Mockery::mock(TranslationLoggerInterface::class);
        $loggerMock
            ->expects('translationMissing')
            ->with(Mockery::type('array'))
            ->once();  // Когда базовый перевод не найден -- логируем один раз
        $this->app->instance(TranslationLoggerInterface::class, $loggerMock);

        $resolver = app(LocalizationResolver::class);
        $guiMessage = $resolver->getLocalizedMessage($module, $baseKey);

        $this->assertSame('no_translation', $guiMessage);
    }
}
