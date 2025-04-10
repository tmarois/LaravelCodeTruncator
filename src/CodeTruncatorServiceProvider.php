<?php

namespace Tmarois\LaravelCodeTruncator;

use Illuminate\Support\ServiceProvider;
use Tmarois\LaravelCodeTruncator\Console\Commands\TruncateCode;
use Tmarois\LaravelCodeTruncator\Services\CodeTruncatorService;

class CodeTruncatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CodeTruncatorService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TruncateCode::class,
            ]);
        }
    }
}
