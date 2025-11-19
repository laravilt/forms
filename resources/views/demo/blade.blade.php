<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravilt Forms - Blade Demo</title>

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles - Load main app CSS and demo JS -->
    @vite(['resources/css/app.css', 'packages/forms/resources/js/demo.js'])
</head>
<body class="font-sans antialiased">
    <div id="app" class="min-h-full">
        <!-- Header -->
        <header class="border-b border-border bg-card">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold">Laravilt Forms</h1>
                        <span class="ml-3 inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20">
                            Blade Demo
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('forms.demo.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Vue/Inertia Demo
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-primary/5 p-4 border border-primary/20">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Card -->
                <div class="bg-card shadow-sm ring-1 ring-border sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold leading-6">
                                Blade Components Demo
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                This demo uses Blade components with Vue.js integration (Splade-like pattern)
                            </p>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('forms.demo.blade.submit') }}" enctype="multipart/form-data" id="demo-form">
                            @csrf

                            <!-- Error Summary -->
                            @if (isset($errors) && $errors->any())
                                <div class="mb-6 rounded-lg bg-destructive/10 p-4 border border-destructive/20">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-destructive" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-destructive">
                                                There were {{ $errors->count() }} error(s) with your submission
                                            </h3>
                                            <div class="mt-2 text-sm text-destructive/80">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-6">
                                @foreach($formSchema->getSchema() as $component)
                                    {!! $component->render() !!}
                                @endforeach
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8 flex items-center justify-end gap-x-3 border-t border-border pt-6">
                                <button
                                    type="button"
                                    onclick="document.getElementById('demo-form').reset()"
                                    class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10"
                                >
                                    Reset
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10"
                                >
                                    Submit Form
                                </button>
                            </div>
                        </form>

                        <!-- Debug: Submitted Data -->
                        @if (session('data'))
                            <div class="mt-8 border-t border-border pt-8">
                                <h4 class="text-sm font-semibold mb-3">Submitted Data:</h4>
                                <pre class="bg-muted p-4 rounded-md overflow-x-auto text-xs"><code>{{ json_encode(session('data'), JSON_PRETTY_PRINT) }}</code></pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
