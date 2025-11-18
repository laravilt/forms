<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * File Upload Field
 *
 * A file upload field with support for:
 * - Single/multiple file upload
 * - Drag and drop
 * - File type restrictions
 * - File size limits
 * - Image preview
 * - Progress tracking
 */
class FileUpload extends Field
{
    protected string $view = 'laravilt-forms::components.fields.file-upload';

    protected bool $multiple = false;

    protected ?int $maxFiles = null;

    protected ?int $maxSize = null; // in KB

    protected array $acceptedFileTypes = [];

    protected bool $image = false;

    protected bool $avatar = false;

    protected bool $preserveFilenames = false;

    protected ?string $directory = null;

    protected string $disk = 'public';

    protected string $visibility = 'public';

    protected ?string $collection = null;

    protected ?Closure $uploadUsing = null;

    protected ?Closure $deleteUsing = null;

    protected bool $native = false;

    /**
     * Use native HTML file input instead of FilePond.
     */
    public function native(bool $condition = true): static
    {
        $this->native = $condition;

        return $this;
    }

    /**
     * Check if using native input.
     */
    public function isNative(): bool
    {
        return $this->native;
    }

    /**
     * Allow multiple file uploads.
     */
    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    /**
     * Check if multiple uploads are allowed.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Set maximum number of files.
     */
    public function maxFiles(int $max): static
    {
        $this->maxFiles = $max;
        $this->multiple = true;

        return $this;
    }

    /**
     * Get maximum files.
     */
    public function getMaxFiles(): ?int
    {
        return $this->maxFiles;
    }

    /**
     * Set maximum file size in KB.
     */
    public function maxSize(int $sizeInKB): static
    {
        $this->maxSize = $sizeInKB;

        return $this;
    }

    /**
     * Get maximum file size.
     */
    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }

    /**
     * Set accepted file types (MIME types or extensions).
     */
    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    /**
     * Get accepted file types.
     */
    public function getAcceptedFileTypes(): array
    {
        if ($this->image) {
            return ['image/*'];
        }

        return $this->acceptedFileTypes;
    }

    /**
     * Make this an image upload field.
     */
    public function image(bool $condition = true): static
    {
        $this->image = $condition;

        if ($condition) {
            $this->acceptedFileTypes(['image/*']);
        }

        return $this;
    }

    /**
     * Check if this is an image upload.
     */
    public function isImage(): bool
    {
        return $this->image;
    }

    /**
     * Make this an avatar upload (circular image).
     */
    public function avatar(bool $condition = true): static
    {
        $this->avatar = $condition;
        $this->image = $condition;

        return $this;
    }

    /**
     * Check if this is an avatar upload.
     */
    public function isAvatar(): bool
    {
        return $this->avatar;
    }

    /**
     * Preserve original filenames.
     */
    public function preserveFilenames(bool $condition = true): static
    {
        $this->preserveFilenames = $condition;

        return $this;
    }

    /**
     * Check if filenames should be preserved.
     */
    public function shouldPreserveFilenames(): bool
    {
        return $this->preserveFilenames;
    }

    /**
     * Set storage directory.
     */
    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get storage directory.
     */
    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    /**
     * Set storage disk.
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Get storage disk.
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Set file visibility.
     */
    public function visibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get file visibility.
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * Set media library collection.
     */
    public function collection(string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get media library collection.
     */
    public function getCollection(): ?string
    {
        return $this->collection;
    }

    /**
     * Customize upload handling.
     */
    public function uploadUsing(Closure $callback): static
    {
        $this->uploadUsing = $callback;

        return $this;
    }

    /**
     * Customize delete handling.
     */
    public function deleteUsing(Closure $callback): static
    {
        $this->deleteUsing = $callback;

        return $this;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'multiple' => $this->isMultiple(),
            'maxFiles' => $this->getMaxFiles(),
            'maxSize' => $this->getMaxSize(),
            'acceptedFileTypes' => $this->getAcceptedFileTypes(),
            'image' => $this->isImage(),
            'avatar' => $this->isAvatar(),
            'directory' => $this->getDirectory(),
            'disk' => $this->getDisk(),
            'native' => $this->isNative(),
        ]);
    }
}
