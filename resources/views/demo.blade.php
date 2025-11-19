<!DOCTYPE html>
<html lang="en" lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravilt Forms - Component Demo</title>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-background antialiased">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-card border-b border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <h1 class="text-3xl font-bold text-foreground">
                    Laravilt Forms - Component Demo
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Comprehensive demonstration with Sections, Tabs, and Grid layouts
                </p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-emerald-800 dark:text-emerald-200">
                                Success!
                            </h3>
                            <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                                {{ session('success') }}
                            </p>

                            @if(session('data'))
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-sm font-medium text-emerald-800 dark:text-emerald-200 hover:text-emerald-900 dark:hover:text-emerald-100">
                                        View submitted data
                                    </summary>
                                    <pre class="mt-2 text-xs bg-white dark:bg-gray-900 p-3 rounded border border-emerald-200 dark:border-emerald-800 overflow-x-auto">{{ json_encode(session('data'), JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <div class="bg-card rounded-lg border border-border shadow-sm">
                <form action="{{ route('forms.demo.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="p-6">
                        <x-laravilt-form :schema="$formSchema->getSchema()" />
                    </div>

                    <div class="px-6 py-4 bg-muted/50 border-t border-border rounded-b-lg flex items-center justify-between">
                        <button
                            type="reset"
                            class="inline-flex items-center px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            Reset
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent bg-primary text-primary-foreground hover:bg-primary/90 rounded-md text-sm font-medium shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            Submit Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Card -->
            <div class="mt-8 bg-card rounded-lg border border-border p-6">
                <h2 class="text-lg font-semibold text-foreground mb-3">
                    Schema Layout Components Demo
                </h2>
                <div class="space-y-2 text-sm text-muted-foreground">
                    <p>✅ <strong>Tabs:</strong> 4 tabs organizing different form sections</p>
                    <p>✅ <strong>Sections:</strong> Multiple sections with headings, descriptions, and icons</p>
                    <p>✅ <strong>Grid:</strong> Responsive 2-column grids within sections</p>
                    <p>✅ <strong>Collapsible:</strong> Some sections can be collapsed/expanded</p>
                    <p>✅ <strong>shadcn-vue styling:</strong> Matches Laravel Vue starter kit design</p>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
