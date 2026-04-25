<?php

namespace SeaPay\LaravelSeaPay;

use Illuminate\Support\ServiceProvider;
use SeaPay\LaravelSeaPay\AccountResolvers\ChainAccountResolver;
use SeaPay\LaravelSeaPay\AccountResolvers\ConfigAccountResolver;
use SeaPay\LaravelSeaPay\AccountResolvers\DatabaseAccountResolver;
use SeaPay\LaravelSeaPay\Contracts\AccountResolverInterface;
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

        $this->app->singleton(AccountResolverInterface::class, function ($app) {
            return $this->buildResolver($app['config']['seapay']);
        });

        $this->app->singleton(SeaPayInterface::class, function ($app) {
            return new SeaPayManager(
                $app['config']['seapay'],
                $app->make(SeaPayClient::class),
                $app->make(AccountResolverInterface::class),
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
        $this->registerViews();
        $this->registerMiddleware();
    }

    private function buildResolver(array $config): AccountResolverInterface
    {
        $driver = $config['account_resolver']['driver'] ?? 'config';

        return match ($driver) {
            'database' => new DatabaseAccountResolver($config),
            'chain'    => new ChainAccountResolver([
                new DatabaseAccountResolver($config),
                new ConfigAccountResolver($config),
            ]),
            'custom'   => $this->buildCustomResolver($config),
            default    => new ConfigAccountResolver($config),
        };
    }

    private function buildCustomResolver(array $config): AccountResolverInterface
    {
        $class = $config['account_resolver']['class'] ?? null;

        if (!$class || !class_exists($class)) {
            throw new \InvalidArgumentException(
                "SeaPay: custom account resolver '{$class}' không tồn tại. " .
                "Hãy khai báo 'class' trong config seapay.account_resolver."
            );
        }

        $resolver = $this->app->make($class);

        if (!$resolver instanceof AccountResolverInterface) {
            throw new \InvalidArgumentException(
                "SeaPay: '{$class}' phải implement AccountResolverInterface."
            );
        }

        return $resolver;
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'seapay');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/seapay'),
        ], 'seapay-views');
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
        $this->app['router']->aliasMiddleware('seapay.webhook', VerifySeaPayWebhook::class);
    }
}
