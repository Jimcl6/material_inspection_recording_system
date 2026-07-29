<?php

namespace Tests\Unit;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Vite;
use Tests\TestCase;

class ProductionViteConfigurationTest extends TestCase
{
    public function test_production_ignores_the_public_hot_file(): void
    {
        $this->app->instance('env', 'production');

        $vite = new Vite;
        $this->app->instance(Vite::class, $vite);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame(
            storage_path('framework/vite-production.hot'),
            $vite->hotFile()
        );
        $this->assertNotSame(public_path('hot'), $vite->hotFile());
    }
}
