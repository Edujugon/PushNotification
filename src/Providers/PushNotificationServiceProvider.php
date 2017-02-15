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
            __DIR__.'/../Config/config.php' => $config_path,
            __DIR__.'/../Config/iosCertificates' => config_path('iosCertificates/')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('edujugonPushNotification',function($app)
        {
            return new PushNotification();
        });
    }
}
