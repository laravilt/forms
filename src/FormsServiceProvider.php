<?php

namespace Laravilt\Forms;

use Illuminate\Support\ServiceProvider;

class FormsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravilt-forms.php',
            'laravilt-forms'
        );

        // Register any services, bindings, or singletons here
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'forms');


        // Load web routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');


        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/laravilt-forms.php' => config_path('laravilt-forms.php'),
            ], 'laravilt-forms-config');

            // Publish assets
            $this->publishes([
                __DIR__ . '/../dist' => public_path('vendor/laravilt/forms'),
            ], 'laravilt-forms-assets');


            // Register commands
            $this->commands([
                Commands\InstallFormsCommand::class,
            ]);
        }
    }
}
