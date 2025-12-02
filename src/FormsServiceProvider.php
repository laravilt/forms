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
            __DIR__.'/../config/laravilt-forms.php',
            'laravilt-forms'
        );
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravilt-forms');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravilt-forms');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes (only in local environment)
        if ($this->app->environment('local')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/laravilt-forms.php' => config_path('laravilt-forms.php'),
            ], 'laravilt-forms-config');

            // Publish assets
            $this->publishes([
                __DIR__.'/../public/dist' => public_path('vendor/laravilt/forms'),
            ], 'laravilt-forms-assets');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravilt-forms'),
            ], 'laravilt-forms-views');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'laravilt-forms-migrations');

            // Register commands
            $this->commands([
                Commands\MakeComponentCommand::class,
                Commands\MakeFormCommand::class,
            ]);
        }

        // Register Blade components
        $this->registerBladeComponents();
    }

    /**
     * Register Blade components.
     */
    protected function registerBladeComponents(): void
    {
        $this->loadViewComponentsAs('laravilt', [
            // Layout Components
            \Laravilt\Forms\View\Components\Form::class,
            \Laravilt\Forms\View\Components\FieldWrapper::class,

            // Basic Fields
            \Laravilt\Forms\Components\TextInput::class,
            \Laravilt\Forms\Components\Textarea::class,
            \Laravilt\Forms\Components\Select::class,
            \Laravilt\Forms\Components\Checkbox::class,
            \Laravilt\Forms\Components\Radio::class,
            \Laravilt\Forms\Components\Toggle::class,
            \Laravilt\Forms\Components\DatePicker::class,
            \Laravilt\Forms\Components\TimePicker::class,
            \Laravilt\Forms\Components\DateTimePicker::class,
            \Laravilt\Forms\Components\FileUpload::class,
            \Laravilt\Forms\Components\Hidden::class,

            // Advanced Fields
            \Laravilt\Forms\Components\ColorPicker::class,
            \Laravilt\Forms\Components\TagsInput::class,
            \Laravilt\Forms\Components\KeyValue::class,
            \Laravilt\Forms\Components\Repeater::class,
            \Laravilt\Forms\Components\RichEditor::class,
            \Laravilt\Forms\Components\MarkdownEditor::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
