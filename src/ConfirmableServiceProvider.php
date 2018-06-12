<?php

namespace Tyler36\ConfirmableTrait;

use Illuminate\Support\ServiceProvider;

class ConfirmableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Model
        $this->publishes([__DIR__.'/model/Confirmation.php' => app_path('Confirmation.php')]);

        // Database
        $this->publishes([__DIR__.'/database/factories' => database_path('factories')]);
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Route
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->publishes([
            __DIR__.'/Http/Controllers'   => app_path('Http/Controllers'),
            __DIR__.'/Http/Requests'      => app_path('Http/Requests'),
        ]);

        // Notifications
        $this->publishes([__DIR__.'/notifications' => app_path('Notifications')]);

        // Views
        $this->publishes([__DIR__.'/views' => resource_path('views')]);

        // Translations
        $this->loadTranslationsFrom(__DIR__.'/lang', 'confirmable');
        $this->publishes([__DIR__.'/lang' => resource_path('lang/vendor/confirmable')]);
    }

    public function register()
    {
    }
}
