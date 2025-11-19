<?php

namespace Laravilt\Forms\Components\CodeEditor;

enum Language: string
{
    case JavaScript = 'javascript';
    case TypeScript = 'typescript';
    case PHP = 'php';
    case Python = 'python';
    case Java = 'java';
    case CSharp = 'csharp';
    case CPlusPlus = 'cpp';
    case C = 'c';
    case Ruby = 'ruby';
    case Go = 'go';
    case Rust = 'rust';
    case Swift = 'swift';
    case Kotlin = 'kotlin';
    case HTML = 'html';
    case CSS = 'css';
    case SCSS = 'scss';
    case SASS = 'sass';
    case Less = 'less';
    case JSON = 'json';
    case XML = 'xml';
    case YAML = 'yaml';
    case Markdown = 'markdown';
    case SQL = 'sql';
    case Shell = 'shell';
    case Bash = 'bash';
    case PowerShell = 'powershell';
    case Dockerfile = 'dockerfile';
    case Vue = 'vue';
    case React = 'jsx';
    case Svelte = 'svelte';
    case GraphQL = 'graphql';
}
