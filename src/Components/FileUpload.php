<?php

namespace Laravilt\Forms\Components;

use Closure;

class FileUpload extends Field
{
    protected string $view = 'laravilt-forms::components.fields.file-upload';

    protected string|Closure|null $disk = null;

    protected string|Closure|null $directory = null;

    protected string|Closure|null $visibility = null;

    protected array|Closure $acceptedFileTypes = [];

    protected int|Closure|null $maxSize = null;

    protected int|Closure|null $minSize = null;

    protected int|Closure|null $maxFiles = 1;

    protected bool|Closure $multiple = false;

    protected bool $image = false;

    protected bool|Closure $imagePreview = true;

    protected int|Closure|null $imageResizeWidth = null;

    protected int|Closure|null $imageResizeHeight = null;

    protected string|Closure|null $imageResizeMode = null;

    protected bool $imageCrop = false;

    protected string|Closure|null $imageCropAspectRatio = null;

    protected bool|Closure $pannable = true;

    protected bool|Closure $downloadable = false;

    protected bool|Closure $deletable = true;

    protected bool|Closure $reorderable = false;

    protected bool|Closure $openable = false;

    protected bool|Closure $previewable = true;

    protected bool $useMediaLibrary = false;

    protected ?string $collection = null;

    // Additional Filament-compatible properties
    protected bool|Closure $preserveFilenames = false;

    protected ?Closure $getUploadedFileNameForStorage = null;

    protected string|Closure|null $storeFileNamesIn = null;

    protected bool|Closure $avatar = false;

    protected bool|Closure $imageEditor = false;

    protected array|Closure|null $imageEditorAspectRatios = null;

    protected int|Closure|null $imageEditorMode = null;

    protected string|Closure|null $imageEditorEmptyFillColor = null;

    protected string|Closure|null $imageEditorViewportWidth = null;

    protected string|Closure|null $imageEditorViewportHeight = null;

    protected bool|Closure $circleCropper = false;

    protected string|Closure|null $imagePreviewHeight = null;

    protected string|Closure|null $loadingIndicatorPosition = null;

    protected string|Closure|null $panelAspectRatio = null;

    protected string|Closure|null $panelLayout = null;

    protected string|Closure|null $removeUploadedFileButtonPosition = null;

    protected string|Closure|null $uploadButtonPosition = null;

    protected string|Closure|null $uploadProgressIndicatorPosition = null;

    protected bool|Closure $appendFiles = false;

    protected bool|Closure $prependFiles = false;

    protected bool|Closure $moveFiles = true;

    protected bool|Closure $storeFiles = true;

    protected bool|Closure $orientImagesFromExif = true;

    protected bool|Closure $alignCenter = false;

    protected bool|Closure $pasteable = true;

    protected bool|Closure $fetchFileInformation = true;

    protected string|Closure|null $uploadingMessage = null;

    protected array|Closure|null $mimeTypeMap = null;

    protected int|Closure|null $minFiles = null;

    protected int|Closure|null $maxParallelUploads = null;

    /**
     * Enable media library storage with optional collection name.
     */
    public function collection(?string $collection = 'default'): static
    {
        $this->useMediaLibrary = true;
        $this->collection = $collection;

        return $this;
    }

    /**
     * Check if media library is enabled.
     */
    public function usesMediaLibrary(): bool
    {
        return $this->useMediaLibrary;
    }

    /**
     * Get the media library collection name.
     */
    public function getCollection(): ?string
    {
        return $this->collection;
    }

    /**
     * Check if multiple files are allowed.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Set the storage disk.
     */
    public function disk(string|Closure|null $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set the storage directory.
     */
    public function directory(string|Closure|null $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Set the file visibility (public or private).
     */
    public function visibility(string|Closure|null $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Set accepted file types (MIME types or extensions).
     */
    public function acceptedFileTypes(array|Closure $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    /**
     * Set maximum file size in kilobytes.
     */
    public function maxSize(int|Closure|null $sizeInKb): static
    {
        $this->maxSize = $sizeInKb;

        return $this;
    }

    /**
     * Set minimum file size in kilobytes.
     */
    public function minSize(int|Closure|null $sizeInKb): static
    {
        $this->minSize = $sizeInKb;

        return $this;
    }

    /**
     * Set maximum number of files.
     */
    public function maxFiles(int|Closure|null $count): static
    {
        $this->maxFiles = $count;

        return $this;
    }

    /**
     * Set minimum number of files.
     */
    public function minFiles(int|Closure|null $count): static
    {
        $this->minFiles = $count;

        return $this;
    }

    /**
     * Allow multiple file uploads.
     */
    public function multiple(bool|Closure $condition = true): static
    {
        $this->multiple = $condition;

        if ($condition === true && $this->maxFiles === 1) {
            $this->maxFiles = null;
        }

        return $this;
    }

    /**
     * Set maximum parallel uploads.
     */
    public function maxParallelUploads(int|Closure $count): static
    {
        $this->maxParallelUploads = $count;

        return $this;
    }

    /**
     * Configure for image uploads.
     */
    public function image(bool $condition = true): static
    {
        $this->image = $condition;

        if ($condition) {
            $this->acceptedFileTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        }

        return $this;
    }

    /**
     * Enable/disable image preview.
     */
    public function imagePreview(bool|Closure $condition = true): static
    {
        $this->imagePreview = $condition;

        return $this;
    }

    /**
     * Set image resize dimensions.
     */
    public function imageResize(?int $width = 1920, ?int $height = 1080, string $mode = 'contain'): static
    {
        $this->imageResizeWidth = $width;
        $this->imageResizeHeight = $height;
        $this->imageResizeMode = $mode;

        return $this;
    }

    /**
     * Enable image cropping.
     */
    public function imageCrop(bool|float|null $aspectRatio = true): static
    {
        if ($aspectRatio === false) {
            $this->imageCrop = false;
            $this->imageCropAspectRatio = null;
        } elseif (is_float($aspectRatio) || is_int($aspectRatio)) {
            $this->imageCrop = true;
            $this->imageCropAspectRatio = (float) $aspectRatio;
        } else {
            $this->imageCrop = true;
            $this->imageCropAspectRatio = null;
        }

        return $this;
    }

    /**
     * Enable/disable pan functionality.
     */
    public function pannable(bool|Closure $condition = true): static
    {
        $this->pannable = $condition;

        return $this;
    }

    /**
     * Enable/disable file download.
     */
    public function downloadable(bool|Closure $condition = true): static
    {
        $this->downloadable = $condition;

        return $this;
    }

    /**
     * Enable/disable file deletion.
     */
    public function deletable(bool|Closure $condition = true): static
    {
        $this->deletable = $condition;

        return $this;
    }

    /**
     * Enable/disable file reordering.
     */
    public function reorderable(bool|Closure $condition = true): static
    {
        $this->reorderable = $condition;

        return $this;
    }

    /**
     * Enable/disable file opening in new tab.
     */
    public function openable(bool|Closure $condition = true): static
    {
        $this->openable = $condition;

        return $this;
    }

    /**
     * Enable/disable file preview.
     */
    public function previewable(bool|Closure $condition = true): static
    {
        $this->previewable = $condition;

        return $this;
    }

    /**
     * Preserve original filenames when storing.
     */
    public function preserveFilenames(bool|Closure $condition = true): static
    {
        $this->preserveFilenames = $condition;

        return $this;
    }

    /**
     * Set a custom callback for generating uploaded file names.
     */
    public function getUploadedFileNameForStorageUsing(?Closure $callback): static
    {
        $this->getUploadedFileNameForStorage = $callback;

        return $this;
    }

    /**
     * Store original filenames in a separate field.
     */
    public function storeFileNamesIn(string|Closure|null $statePath): static
    {
        $this->storeFileNamesIn = $statePath;

        return $this;
    }

    /**
     * Configure as avatar upload (circular with 1:1 aspect ratio).
     */
    public function avatar(): static
    {
        $this->avatar = true;
        $this->imageCropAspectRatio = '1:1';
        $this->circleCropper = true;
        $this->imagePreview = true;
        $this->maxFiles = 1;

        // Auto-center if columnSpanFull is set
        if ($this->columnSpan === 'full') {
            $this->alignCenter = true;
        }

        return $this;
    }

    public function alignCenter(bool $condition = true): static
    {
        $this->alignCenter = $condition;

        return $this;
    }

    /**
     * Enable/disable image editor.
     */
    public function imageEditor(bool|Closure $condition = true): static
    {
        $this->imageEditor = $condition;

        return $this;
    }

    /**
     * Set image editor aspect ratios.
     */
    public function imageEditorAspectRatios(array|Closure|null $ratios): static
    {
        $this->imageEditorAspectRatios = $ratios;

        return $this;
    }

    /**
     * Set image editor mode.
     */
    public function imageEditorMode(int|Closure $mode): static
    {
        $this->imageEditorMode = $mode;

        return $this;
    }

    /**
     * Set image editor empty fill color.
     */
    public function imageEditorEmptyFillColor(string|Closure $color): static
    {
        $this->imageEditorEmptyFillColor = $color;

        return $this;
    }

    /**
     * Set image editor viewport width.
     */
    public function imageEditorViewportWidth(string|Closure $width): static
    {
        $this->imageEditorViewportWidth = $width;

        return $this;
    }

    /**
     * Set image editor viewport height.
     */
    public function imageEditorViewportHeight(string|Closure $height): static
    {
        $this->imageEditorViewportHeight = $height;

        return $this;
    }

    /**
     * Enable/disable circle cropper.
     */
    public function circleCropper(bool|Closure $condition = true): static
    {
        $this->circleCropper = $condition;

        return $this;
    }

    /**
     * Set image crop aspect ratio (Filament-compatible method).
     */
    public function imageCropAspectRatio(string|Closure|null $ratio): static
    {
        $this->imageCropAspectRatio = $ratio;

        return $this;
    }

    /**
     * Set image resize target height.
     */
    public function imageResizeTargetHeight(string|Closure|null $height): static
    {
        $this->imageResizeHeight = $height;

        return $this;
    }

    /**
     * Set image resize target width.
     */
    public function imageResizeTargetWidth(string|Closure|null $width): static
    {
        $this->imageResizeWidth = $width;

        return $this;
    }

    /**
     * Set image resize mode (force/cover/contain).
     */
    public function imageResizeMode(string|Closure|null $mode): static
    {
        $this->imageResizeMode = $mode;

        return $this;
    }

    /**
     * Set image preview height.
     */
    public function imagePreviewHeight(string|Closure $height): static
    {
        $this->imagePreviewHeight = $height;

        return $this;
    }

    /**
     * Set loading indicator position.
     */
    public function loadingIndicatorPosition(string|Closure $position): static
    {
        $this->loadingIndicatorPosition = $position;

        return $this;
    }

    /**
     * Set panel aspect ratio.
     */
    public function panelAspectRatio(string|Closure $ratio): static
    {
        $this->panelAspectRatio = $ratio;

        return $this;
    }

    /**
     * Set panel layout.
     */
    public function panelLayout(string|Closure $layout): static
    {
        $this->panelLayout = $layout;

        return $this;
    }

    /**
     * Set remove uploaded file button position.
     */
    public function removeUploadedFileButtonPosition(string|Closure $position): static
    {
        $this->removeUploadedFileButtonPosition = $position;

        return $this;
    }

    /**
     * Set upload button position.
     */
    public function uploadButtonPosition(string|Closure $position): static
    {
        $this->uploadButtonPosition = $position;

        return $this;
    }

    /**
     * Set upload progress indicator position.
     */
    public function uploadProgressIndicatorPosition(string|Closure $position): static
    {
        $this->uploadProgressIndicatorPosition = $position;

        return $this;
    }

    /**
     * Enable/disable appending files to existing ones.
     */
    public function appendFiles(bool|Closure $condition = true): static
    {
        $this->appendFiles = $condition;

        return $this;
    }

    /**
     * Enable/disable prepending files to existing ones.
     */
    public function prependFiles(bool|Closure $condition = true): static
    {
        $this->prependFiles = $condition;

        return $this;
    }

    /**
     * Enable/disable moving files on the server.
     */
    public function moveFiles(bool|Closure $condition = true): static
    {
        $this->moveFiles = $condition;

        return $this;
    }

    /**
     * Enable/disable storing files.
     */
    public function storeFiles(bool|Closure $condition = true): static
    {
        $this->storeFiles = $condition;

        return $this;
    }

    /**
     * Enable/disable orienting images from EXIF data.
     */
    public function orientImagesFromExif(bool|Closure $condition = true): static
    {
        $this->orientImagesFromExif = $condition;

        return $this;
    }

    /**
     * Enable/disable pasting files.
     */
    public function pasteable(bool|Closure $condition = true): static
    {
        $this->pasteable = $condition;

        return $this;
    }

    /**
     * Enable/disable fetching file information.
     */
    public function fetchFileInformation(bool|Closure $condition = true): static
    {
        $this->fetchFileInformation = $condition;

        return $this;
    }

    /**
     * Set uploading message.
     */
    public function uploadingMessage(string|Closure $message): static
    {
        $this->uploadingMessage = $message;

        return $this;
    }

    /**
     * Set MIME type map.
     */
    public function mimeTypeMap(array|Closure $map): static
    {
        $this->mimeTypeMap = $map;

        return $this;
    }

    /**
     * Get the accepted file types.
     */
    protected function getAcceptedFileTypes(): array
    {
        return $this->evaluate($this->acceptedFileTypes);
    }

    /**
     * Get the disk name.
     */
    protected function getDisk(): string
    {
        return $this->evaluate($this->disk) ?? 'public';
    }

    /**
     * Get the directory.
     */
    protected function getDirectory(): ?string
    {
        return $this->evaluate($this->directory);
    }

    /**
     * Get the visibility.
     */
    protected function getVisibility(): string
    {
        return $this->evaluate($this->visibility) ?? 'public';
    }

    protected function getVueComponent(): string
    {
        return 'FileUpload';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'disk' => $this->getDisk(),
            'directory' => $this->getDirectory(),
            'visibility' => $this->getVisibility(),
            'acceptedFileTypes' => $this->getAcceptedFileTypes(),
            'maxSize' => $this->evaluate($this->maxSize),
            'minSize' => $this->evaluate($this->minSize),
            'maxFiles' => $this->evaluate($this->maxFiles),
            'minFiles' => $this->evaluate($this->minFiles),
            'multiple' => $this->evaluate($this->multiple),
            'image' => $this->image,
            'imagePreview' => $this->evaluate($this->imagePreview),
            'imageResize' => [
                'width' => $this->evaluate($this->imageResizeWidth),
                'height' => $this->evaluate($this->imageResizeHeight),
                'mode' => $this->evaluate($this->imageResizeMode),
            ],
            'imageCrop' => $this->imageCrop,
            'imageCropAspectRatio' => $this->evaluate($this->imageCropAspectRatio),
            'pannable' => $this->evaluate($this->pannable),
            'downloadable' => $this->evaluate($this->downloadable),
            'deletable' => $this->evaluate($this->deletable),
            'reorderable' => $this->evaluate($this->reorderable),
            'openable' => $this->evaluate($this->openable),
            'previewable' => $this->evaluate($this->previewable),
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState(),
            'useMediaLibrary' => $this->useMediaLibrary,
            'collection' => $this->collection,
            // Additional Filament-compatible properties
            'preserveFilenames' => $this->evaluate($this->preserveFilenames),
            'storeFileNamesIn' => $this->evaluate($this->storeFileNamesIn),
            'avatar' => $this->evaluate($this->avatar),
            'imageEditor' => $this->evaluate($this->imageEditor),
            'imageEditorAspectRatios' => $this->evaluate($this->imageEditorAspectRatios),
            'imageEditorMode' => $this->evaluate($this->imageEditorMode),
            'imageEditorEmptyFillColor' => $this->evaluate($this->imageEditorEmptyFillColor),
            'imageEditorViewportWidth' => $this->evaluate($this->imageEditorViewportWidth),
            'imageEditorViewportHeight' => $this->evaluate($this->imageEditorViewportHeight),
            'circleCropper' => $this->evaluate($this->circleCropper),
            'imagePreviewHeight' => $this->evaluate($this->imagePreviewHeight),
            'loadingIndicatorPosition' => $this->evaluate($this->loadingIndicatorPosition),
            'panelAspectRatio' => $this->evaluate($this->panelAspectRatio),
            'panelLayout' => $this->evaluate($this->panelLayout),
            'removeUploadedFileButtonPosition' => $this->evaluate($this->removeUploadedFileButtonPosition),
            'uploadButtonPosition' => $this->evaluate($this->uploadButtonPosition),
            'uploadProgressIndicatorPosition' => $this->evaluate($this->uploadProgressIndicatorPosition),
            'appendFiles' => $this->evaluate($this->appendFiles),
            'prependFiles' => $this->evaluate($this->prependFiles),
            'moveFiles' => $this->evaluate($this->moveFiles),
            'storeFiles' => $this->evaluate($this->storeFiles),
            'orientImagesFromExif' => $this->evaluate($this->orientImagesFromExif),
            'pasteable' => $this->evaluate($this->pasteable),
            'fetchFileInformation' => $this->evaluate($this->fetchFileInformation),
            'uploadingMessage' => $this->evaluate($this->uploadingMessage),
            'mimeTypeMap' => $this->evaluate($this->mimeTypeMap),
            'maxParallelUploads' => $this->evaluate($this->maxParallelUploads),
            'alignCenter' => $this->evaluate($this->alignCenter),
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltFileUpload';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'maxSize' => $this->maxSize,
            'maxFiles' => $this->maxFiles,
            'multiple' => $this->multiple,
            'acceptedFileTypes' => $this->getAcceptedFileTypes(),
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState(),
        ];
    }
}
