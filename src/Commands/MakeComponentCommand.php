<?php

namespace Laravilt\Forms\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Whether handle() stopped on a collision and the command must exit with a failure code.
     */
    protected bool $hasFailed = false;

    /**
     * Execute the console command.
     *
     * Keeps GeneratorCommand's bool|null contract; the exit code is derived in execute().
     */
    public function handle(): ?bool
    {
        $this->hasFailed = false;

        // GeneratorCommand::handle() returns false when the class already exists
        if (parent::handle() === false) {
            return $this->markFailed();
        }

        $this->components->info("Form component [{$this->argument('name')}] created successfully.");

        if ($this->option('vue') && ! $this->createVueComponent()) {
            return $this->markFailed();
        }

        if ($this->option('react') && ! $this->createReactComponent()) {
            return $this->markFailed();
        }

        // Show usage example
        $this->newLine();
        $this->components->bulletList([
            'Import: use App\Forms\Components\\'.str_replace('/', '\\', $this->argument('name')).';',
            'Usage: '.class_basename($this->argument('name')).'::make(\'field_name\')->label(\'Label\')',
        ]);

        return null;
    }

    /**
     * Translate a failed handle() into a non-zero exit code (a `false` return would exit with 0).
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = parent::execute($input, $output);

        return $this->hasFailed ? self::FAILURE : $status;
    }

    /**
     * Record the failure and return GeneratorCommand's failure value.
     */
    protected function markFailed(): bool
    {
        $this->hasFailed = true;

        return false;
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
    protected function createVueComponent(): bool
    {
        $name = class_basename($this->argument('name'));
        $kebabName = Str::kebab($name);

        $path = resource_path("js/components/forms/{$kebabName}.vue");

        if (file_exists($path) && ! $this->option('force')) {
            $this->components->error("Vue component already exists at {$path}");

            return false;
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

        return true;
    }

    /**
     * Create the React component file.
     */
    protected function createReactComponent(): bool
    {
        $name = class_basename($this->argument('name'));
        $kebabName = Str::kebab($name);

        $path = resource_path("js/components/forms/{$kebabName}.tsx");

        if (file_exists($path) && ! $this->option('force')) {
            $this->components->error("React component already exists at {$path}");

            return false;
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

        return true;
    }
}
