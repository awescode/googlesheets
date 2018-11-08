<?php

namespace Awescode\GoogleSheets;

use Awescode\GoogleSheets\Contracts\GoogleSheets as GoogleSheetsContract;
use Awescode\GoogleSheets\GoogleSheets;
use Illuminate\Support\ServiceProvider;

class GoogleSheetsServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/googlesheets.php' => config_path('googlesheets.php'),
        ], 'config');

        if (! class_exists('CreateGoogleSheetsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_googlesheets_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_googlesheets_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GoogleSheetsContract::class, GoogleSheets::class);

        $this->app->singleton('googlesheets', GoogleSheetsContract::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/googlesheets.php',
            'googlesheets'
        );
    }
}
