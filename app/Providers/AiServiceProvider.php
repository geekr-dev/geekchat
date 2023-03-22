<?php

declare(strict_types=1);

namespace App\Providers;

use App\Client\OpenAI;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\OpenAI\ApiKeyIsMissing;

/**
 * @internal
 */
class AiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAI::class, static function (): OpenAI {
            $apiKey = config('openai.api_key');
            $baseUri = config('openai.base_uri');
            $httpProxy = config('openai.http_proxy');
            $organization = config('openai.organization');

            if (!is_string($apiKey) || ($organization !== null && !is_string($organization))) {
                throw ApiKeyIsMissing::create();
            }

            $openAi = new OpenAI($apiKey);
            $openAi->setTimeout(60); // 超时时间默认是60s
            if ($organization !== null) {
                $openAi->setORG($organization);
            }
            // 配置了服务代理地址
            if ($baseUri) {
                $openAi->setBaseURL($baseUri);
            }
            // 配置了本地网络代理
            if ($httpProxy) {
                $openAi->setProxy($httpProxy);
            }
            return $openAi;
        });

        $this->app->alias(OpenAi::class, 'openai');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
