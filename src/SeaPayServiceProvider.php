<?php

namespace SeaPay\LaravelSeaPay;

use Illuminate\Support\ServiceProvider;
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\Http\Middleware\VerifySeaPayWebhook;

class SeaPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/seapay.php', 'seapay');

        $this->app->singleton(SeaPayClient::class, function ($app) {
            return new SeaPayClient($app['config']['seapay']);
        });

        $this->app->singleton(SeaPayInterface::class, function ($app) {
            return new SeaPayManager(
                $app['config']['seapay'],
                $app->make(SeaPayClient::class),
            );
        });

        $this->app->alias(SeaPayInterface::class, 'seapay');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishMigrations();
        }

        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->registerMiddleware();
    }

    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/seapay.php' => config_path('seapay.php'),
        ], 'seapay-config');
    }

    private function publishMigrations(): void
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'seapay-migrations');
    }

    private function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('seapay.webhook', VerifySeaPayWebhook::class);
    }
}
