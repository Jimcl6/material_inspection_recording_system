<?php

namespace App\Providers;

use App\Support\LegacySchemaManager;
use Illuminate\Database\MySqlConnection;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191); // Add this line

        if ($this->app->environment('production')) {
            // Production must never follow a stale public/hot development marker.
            $this->app->make(Vite::class)
                ->useHotFile(storage_path('framework/vite-production.hot'));
        }

        if (! MySqlConnection::hasMacro('getDoctrineSchemaManager')) {
            MySqlConnection::macro('getDoctrineSchemaManager', function (): LegacySchemaManager {
                /** @var MySqlConnection $this */
                return new LegacySchemaManager($this);
            });
        }
    }
}
