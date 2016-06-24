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
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['pushNotification'] = $this->app->share(function($app)
        {
            return new PushNotification();
        });
    }
}
