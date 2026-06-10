<?php

namespace App\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = App::currentLocale();

        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);

        setlocale(LC_TIME, 'es_PE.UTF-8', 'es_PE', 'Spanish_Peru.1252', 'es');
    }
}
