<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Exceptions\OpenAI\ApiKeyIsMissing;
use Orhanerday\OpenAi\OpenAi;

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
        $this->app->singleton(OpenAi::class, static function (): OpenAi {
            $apiKey = config('openai.api_key');
            $baseUri = config('openai.base_uri');
            $httpProxy = config('openai.http_proxy');
            $organization = config('openai.organization');

            if (!is_string($apiKey) || ($organization !== null && !is_string($organization))) {
                throw ApiKeyIsMissing::create();
            }

            $openAi = new OpenAi($apiKey);
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
