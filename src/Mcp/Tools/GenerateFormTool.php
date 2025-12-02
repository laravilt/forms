<?php

namespace Laravilt\Forms\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateFormTool extends Tool
{
    protected string $description = 'Generate a new form class with 30+ field types and validation support';

    public function handle(Request $request): Response
    {
        $name = $request->string('name');

        $command = 'php '.base_path('artisan').' make:form "'.$name.'" --no-interaction';

        if ($request->boolean('resource', false)) {
            $command .= ' --resource';
        }

        if ($request->boolean('force', false)) {
            $command .= ' --force';
        }

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $response = "✅ Form '{$name}' created successfully!\n\n";
            $response .= "📖 Location: app/Forms/{$name}.php\n\n";
            $response .= "📦 Available field types: TextInput, Select, DatePicker, FileUpload, RichEditor, Repeater, and 24+ more\n";

            return Response::text($response);
        } else {
            return Response::text('❌ Failed to create form: '.implode("\n", $output));
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Form class name in StudlyCase (e.g., "UserForm")')
                ->required(),
            'resource' => $schema->boolean()
                ->description('Generate a resource form with CRUD operations')
                ->default(false),
            'force' => $schema->boolean()
                ->description('Overwrite existing file')
                ->default(false),
        ];
    }
}
