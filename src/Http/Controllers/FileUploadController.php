<?php

namespace Laravilt\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileUploadController
{
    /**
     * Handle file upload.
     */
    public function upload(Request $request): HttpResponse
    {
        // Base validation rules
        $validationRules = [
            'file' => ['required', 'file'],
            'disk' => 'nullable|string',
            'directory' => 'nullable|string',
            'visibility' => 'nullable|in:public,private',
        ];

        // Add file type validation if provided
        $acceptedFileTypes = $request->input('acceptedFileTypes');
        if ($acceptedFileTypes && is_array($acceptedFileTypes) && ! empty($acceptedFileTypes)) {
            // Convert MIME types to Laravel validation format
            $mimes = [];
            foreach ($acceptedFileTypes as $type) {
                if (str_starts_with($type, 'image/')) {
                    $mimes[] = str_replace('image/', '', $type);
                } elseif (str_contains($type, '/')) {
                    // Extract extension from MIME type
                    $parts = explode('/', $type);
                    $mimes[] = end($parts);
                }
            }
            if (! empty($mimes)) {
                $validationRules['file'][] = 'mimes:'.implode(',', $mimes);
            }
        }

        // Add file size validation if provided
        $maxSize = $request->input('maxSize'); // in KB
        if ($maxSize && is_numeric($maxSize)) {
            $validationRules['file'][] = 'max:'.$maxSize;
        }

        $minSize = $request->input('minSize'); // in KB
        if ($minSize && is_numeric($minSize)) {
            $validationRules['file'][] = 'min:'.$minSize;
        }

        // Validate the request
        $request->validate($validationRules);

        $file = $request->file('file');
        $disk = $request->input('disk', config('filesystems.default'));
        $directory = $request->input('directory');
        if ($directory === null || $directory === 'null' || $directory === '') {
            $directory = 'uploads';
        }
        $directory = trim((string) $directory, '/');
        $visibility = $request->input('visibility', 'public');

        // Generate unique filename
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $directory ? $directory.'/'.$filename : $filename;

        // Store the file
        $storedPath = Storage::disk($disk)->putFileAs(
            $directory ?: '',
            $file,
            $filename,
            ['visibility' => $visibility]
        );

        // Get the URL
        $url = Storage::disk($disk)->url($storedPath);

        // FilePond expects a plain text response with the unique file identifier
        // We return the stored path which will be used as the serverId
        return response($storedPath, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Handle file deletion.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        $path = $request->input('path');
        $disk = $request->input('disk', config('filesystems.default'));

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }

    /**
     * Get temporary signed URL for private files.
     */
    public function temporaryUrl(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'expiration' => 'nullable|integer',
        ]);

        $path = $request->input('path');
        $disk = $request->input('disk', config('filesystems.default'));
        $expiration = $request->input('expiration', 5); // 5 minutes default

        if (! Storage::disk($disk)->exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        $url = $this->generateTemporaryUrl($request, $disk, $path, $expiration);

        return response()->json([
            'url' => $url,
            'expires_at' => now()->addMinutes($expiration)->toIso8601String(),
        ]);
    }

    /**
     * Serve private files using signed URLs.
     */
    public function servePrivate(Request $request): HttpResponse|StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        $disk = $request->query('disk', config('filesystems.default'));
        $encodedPath = (string) $request->query('path');
        $path = $this->decodePath($encodedPath);

        if (! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        return Storage::disk($disk)->response($path);
    }

    /**
     * Generate a temporary URL for a stored file, with a fallback for local disks.
     */
    protected function generateTemporaryUrl(Request $request, string $disk, string $path, int $expiration): string
    {
        $diskConfig = config("filesystems.disks.{$disk}", []);
        $driver = $diskConfig['driver'] ?? null;

        if ($driver === 'local') {
            $routeName = $this->getPrivateServeRouteName($request);

            if ($routeName === null) {
                throw new \RuntimeException('Private file route is not registered.');
            }

            return URL::temporarySignedRoute(
                $routeName,
                now()->addMinutes($expiration),
                [
                    'disk' => $disk,
                    'path' => $this->encodePath($path),
                ],
                absolute: false
            );
        }

        try {
            return Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes($expiration)
            );
        } catch (\Throwable $exception) {
            $routeName = $this->getPrivateServeRouteName($request);

            if ($routeName === null) {
                throw $exception;
            }

            return URL::temporarySignedRoute(
                $routeName,
                now()->addMinutes($expiration),
                [
                    'disk' => $disk,
                    'path' => $this->encodePath($path),
                ],
                absolute: false
            );
        }
    }

    /**
     * Determine the route name used to serve private files.
     */
    protected function getPrivateServeRouteName(Request $request): ?string
    {
        $currentRoute = $request->route()?->getName();

        if (! $currentRoute) {
            return null;
        }

        $serveRoute = str_replace('temporary-url', 'private', $currentRoute);

        return Route::has($serveRoute) ? $serveRoute : null;
    }

    /**
     * Encode the file path so it can be transported safely.
     */
    protected function encodePath(string $path): string
    {
        return base64_encode($path);
    }

    /**
     * Decode a previously encoded file path.
     */
    protected function decodePath(string $encodedPath): string
    {
        $decoded = base64_decode($encodedPath, true);

        if ($decoded === false) {
            abort(400, 'Invalid file reference.');
        }

        return $decoded;
    }
}
