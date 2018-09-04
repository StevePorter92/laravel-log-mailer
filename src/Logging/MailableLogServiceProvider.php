<?php

namespace DesignMyNight\Laravel\Logging;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;
use Laravel\Lumen\Application as LumenApplication;
use DesignMyNight\Laravel\Logging\Driver\MailableLogger;
use Illuminate\Foundation\Application as LaravelApplication;

class MailableLogServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupViews($this->app);
        $this->setupConfig($this->app);
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function setupConfig(Container $app)
    {
        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/mailablelog.php' => config_path('mailablelog.php'),
            ], 'mailablelog-config');
        } elseif ($app instanceof LumenApplication) {
            $app->configure('mailablelog');
        }
    }

    /**
     * Publish the views.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function setupViews(Container $app)
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mailablelog');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/mailablelog'),
            ], 'mailablelog-views');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app['log'] instanceof LogManager) {
            $this->loadViewsFrom(base_path('designmynight/resources/views'), 'mailablelog');

            $this->app['log']->extend('mail', function (Container $app, array $config) {
                $logger = new MailableLogger();

                return $logger($config);
            });
        }
    }
}
