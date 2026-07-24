<?php

namespace App\Providers;

use App\Support\LegacySchemaManager;
use Illuminate\Database\MySqlConnection;
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

        if (! MySqlConnection::hasMacro('getDoctrineSchemaManager')) {
            MySqlConnection::macro('getDoctrineSchemaManager', function (): LegacySchemaManager {
                /** @var MySqlConnection $this */
                return new LegacySchemaManager($this);
            });
        }
    }
}
