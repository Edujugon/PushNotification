<?php

namespace Edujugon\PushNotification\Providers;

use Edujugon\PushNotification\PushNotification;
use Illuminate\Support\ServiceProvider;

class PushNotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $config_path = function_exists('config_path') ? config_path('pushnotification.php') : 'pushnotification.php';

        $this->publishes([
            __DIR__.'/../Config/config.php' => $config_path
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['edujugonPushNotification'] = $this->app->share(function($app)
        {
            return new PushNotification();
        });
    }
}
