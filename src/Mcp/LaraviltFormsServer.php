<?php

namespace Laravilt\Forms\Mcp;

use Laravel\Mcp\Server;
use Laravilt\Forms\Mcp\Tools\GenerateFormTool;
use Laravilt\Forms\Mcp\Tools\SearchDocsTool;

class LaraviltFormsServer extends Server
{
    protected string $name = 'Laravilt Forms';
    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        This server provides form building capabilities for Laravilt projects.

        You can:
        - Generate new form classes with 30+ field types
        - Generate resource forms for CRUD operations
        - Search forms documentation
        - Access information about field types and validation

        Forms support text, select, date, file upload, rich editors, and more.
    MARKDOWN;

    protected array $tools = [
        GenerateFormTool::class,
        SearchDocsTool::class,
    ];
}
