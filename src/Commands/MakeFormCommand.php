<?php

namespace Laravilt\Forms\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeFormCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:form {name : The name of the form}
                            {--resource : Generate a resource form with CRUD operations}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Form';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();

        $this->components->info("Form [{$this->argument('name')}] created successfully.");

        // Show usage example
        $this->newLine();
        $this->components->bulletList([
            'Import: use App\Forms\\'.str_replace('/', '\\', $this->argument('name')).';',
            'Usage: '.class_basename($this->argument('name')).'::make()->schema([...])',
        ]);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        if ($this->option('resource')) {
            return __DIR__.'/../../stubs/form.resource.stub';
        }

        return __DIR__.'/../../stubs/form.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Forms';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceFormName($stub);
    }

    /**
     * Replace the form name in the stub.
     */
    protected function replaceFormName(string $stub): string
    {
        $name = class_basename($this->argument('name'));
        $kebabName = Str::kebab($name);
        $snakeName = Str::snake($name);

        $stub = str_replace('{{ formKebab }}', $kebabName, $stub);
        $stub = str_replace('{{ formSnake }}', $snakeName, $stub);

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
}
