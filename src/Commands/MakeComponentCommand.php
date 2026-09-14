<?php

namespace Laravilt\Forms\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeComponentCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:form-component {name : The name of the component}
                            {--vue : Also generate Vue component}
                            {--react : Also generate React component}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form component class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Form Component';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();

        $this->components->info("Form component [{$this->argument('name')}] created successfully.");

        if ($this->option('vue')) {
            $this->createVueComponent();
        }

        if ($this->option('react')) {
            $this->createReactComponent();
        }

        // Show usage example
        $this->newLine();
        $this->components->bulletList([
            'Import: use App\Forms\Components\\'.str_replace('/', '\\', $this->argument('name')).';',
            'Usage: '.class_basename($this->argument('name')).'::make(\'field_name\')->label(\'Label\')',
        ]);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/component.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Forms\\Components';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceComponentName($stub);
    }

    /**
     * Replace the component name in the stub.
     */
    protected function replaceComponentName(string $stub): string
    {
        $name = class_basename($this->argument('name'));
        $kebabName = Str::kebab($name);
        $snakeName = Str::snake($name);

        $stub = str_replace('{{ componentKebab }}', $kebabName, $stub);
        $stub = str_replace('{{ componentSnake }}', $snakeName, $stub);

        return $stub;
    }

    /**
     * Get the destination class path.
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Create the Vue component file.
     */
    protected function createVueComponent(): void
    {
        $name = class_basename($this->argument('name'));
        $kebabName = Str::kebab($name);

        $path = resource_path("js/components/forms/{$kebabName}.vue");

        if (file_exists($path) && ! $this->option('force')) {
            $this->components->error("Vue component already exists at {$path}");

            return;
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stub = file_get_contents(__DIR__.'/../../stubs/component.vue.stub');
        $stub = str_replace('{{ componentName }}', $name, $stub);
        $stub = str_replace('{{ componentKebab }}', $kebabName, $stub);

        file_put_contents($path, $stub);

        $this->components->info("Vue component created at {$path}");
    }

    /**
     * Create the React component file.
     */
    protected function createReactComponent(): void
    {
        $name = class_basename($this->argument('name'));
        $kebabName = Str::kebab($name);

        $path = resource_path("js/components/forms/{$kebabName}.tsx");

        if (file_exists($path) && ! $this->option('force')) {
            $this->components->error("React component already exists at {$path}");

            return;
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stub = file_get_contents(__DIR__.'/../../stubs/component.tsx.stub');
        $stub = str_replace('{{ componentName }}', $name, $stub);
        $stub = str_replace('{{ componentKebab }}', $kebabName, $stub);

        file_put_contents($path, $stub);

        $this->components->info("React component created at {$path}");
    }
}
