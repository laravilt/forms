<?php

namespace Laravilt\Forms;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravilt\Forms\Components\Checkbox;
use Laravilt\Forms\Components\ColorPicker;
use Laravilt\Forms\Components\DatePicker;
use Laravilt\Forms\Components\DateTimePicker;
use Laravilt\Forms\Components\FileUpload;
use Laravilt\Forms\Components\Hidden;
use Laravilt\Forms\Components\KeyValue;
use Laravilt\Forms\Components\MarkdownEditor;
use Laravilt\Forms\Components\Radio;
use Laravilt\Forms\Components\Repeater;
use Laravilt\Forms\Components\RichEditor;
use Laravilt\Forms\Components\Select;
use Laravilt\Forms\Components\TagsInput;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\TimePicker;
use Laravilt\Forms\Components\Toggle;
use Laravilt\Forms\View\Components\FieldWrapper;
use Laravilt\Forms\View\Components\Form;

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
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'forms');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Upload and reactive-field endpoints (FileUpload, MarkdownEditor, live fields). These were
        // once registered only when APP_ENV=local, which broke uploads and live fields in production.
        if (config('laravilt-forms.routes.enabled', true) && ! $this->app->routesAreCached()) {
            Route::middleware(
                config('laravilt-forms.routes.middleware', ['web', 'auth', 'throttle:120,1'])
            )->group(__DIR__.'/../routes/web.php');
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
            Form::class,
            FieldWrapper::class,

            // Basic Fields
            TextInput::class,
            Textarea::class,
            Select::class,
            Checkbox::class,
            Radio::class,
            Toggle::class,
            DatePicker::class,
            TimePicker::class,
            DateTimePicker::class,
            FileUpload::class,
            Hidden::class,

            // Advanced Fields
            ColorPicker::class,
            TagsInput::class,
            KeyValue::class,
            Repeater::class,
            RichEditor::class,
            MarkdownEditor::class,
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
