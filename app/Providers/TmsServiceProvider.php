<?php

namespace App\Providers;

use App\Client\TmsService;
use Illuminate\Support\ServiceProvider;

class TmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TmsService::class, static function (): TmsService {
            $secretId = config('tencent.secret_id');
            $secretKey = config('tencent.secret_key');
            $tmsEndpoint = config('tencent.tms_endpoint');
            $region = config('tencent.region');

            return new TmsService($secretId, $secretKey, $tmsEndpoint, $region);
        });

        $this->app->alias(TmsService::class, 'tms');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
