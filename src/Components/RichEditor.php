<?php

namespace Laravilt\Forms\Components;

use Closure;

class RichEditor extends Field
{
    protected string $view = 'laravilt-forms::components.fields.rich-editor';

    protected array|Closure $toolbarButtons = [];

    protected ?int $minHeight = null;

    protected ?int $maxHeight = null;

    protected bool $json = false;

    protected array|Closure $floatingToolbars = [];

    protected array|Closure $textColors = [];

    protected array|Closure $customTextColors = [];

    protected ?string $fileAttachmentsDisk = null;

    protected ?string $fileAttachmentsDirectory = null;

    protected ?string $fileAttachmentsVisibility = null;

    protected array|Closure $fileAttachmentsAcceptedFileTypes = [];

    protected ?int $fileAttachmentsMaxSize = null;

    protected array|Closure $customBlocks = [];

    protected array|Closure $mergeTags = [];

    protected ?string $activePanel = null;

    protected bool $showCharacterCount = false;

    protected bool $showWordCount = false;

    /**
     * Set the toolbar buttons.
     */
    public function toolbarButtons(array|Closure $buttons): static
    {
        $this->toolbarButtons = $buttons;

        return $this;
    }

    /**
     * Set the minimum height in pixels.
     */
    public function minHeight(?int $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    /**
     * Set the maximum height in pixels.
     */
    public function maxHeight(?int $height): static
    {
        $this->maxHeight = $height;

        return $this;
    }

    /**
     * Store content as JSON instead of HTML.
     */
    public function json(bool $condition = true): static
    {
        $this->json = $condition;

        return $this;
    }

    /**
     * Configure floating toolbars for specific node types.
     */
    public function floatingToolbars(array|Closure $toolbars): static
    {
        $this->floatingToolbars = $toolbars;

        return $this;
    }

    /**
     * Set available text colors.
     */
    public function textColors(array|Closure $colors): static
    {
        $this->textColors = $colors;

        return $this;
    }

    /**
     * Set custom text colors.
     */
    public function customTextColors(array|Closure $colors): static
    {
        $this->customTextColors = $colors;

        return $this;
    }

    /**
     * Set the disk for file attachments.
     */
    public function fileAttachmentsDisk(?string $disk): static
    {
        $this->fileAttachmentsDisk = $disk;

        return $this;
    }

    /**
     * Set the directory for file attachments.
     */
    public function fileAttachmentsDirectory(?string $directory): static
    {
        $this->fileAttachmentsDirectory = $directory;

        return $this;
    }

    /**
     * Set the visibility for file attachments.
     */
    public function fileAttachmentsVisibility(?string $visibility): static
    {
        $this->fileAttachmentsVisibility = $visibility;

        return $this;
    }

    /**
     * Set accepted file types for attachments.
     */
    public function fileAttachmentsAcceptedFileTypes(array|Closure $types): static
    {
        $this->fileAttachmentsAcceptedFileTypes = $types;

        return $this;
    }

    /**
     * Set maximum file size for attachments in KB.
     */
    public function fileAttachmentsMaxSize(?int $size): static
    {
        $this->fileAttachmentsMaxSize = $size;

        return $this;
    }

    /**
     * Configure custom blocks.
     */
    public function customBlocks(array|Closure $blocks): static
    {
        $this->customBlocks = $blocks;

        return $this;
    }

    /**
     * Configure merge tags.
     */
    public function mergeTags(array|Closure $tags): static
    {
        $this->mergeTags = $tags;

        return $this;
    }

    /**
     * Set the active panel (customBlocks or mergeTags).
     */
    public function activePanel(?string $panel): static
    {
        $this->activePanel = $panel;

        return $this;
    }

    /**
     * Show character count.
     */
    public function showCharacterCount(bool $condition = true): static
    {
        $this->showCharacterCount = $condition;

        return $this;
    }

    /**
     * Show word count.
     */
    public function showWordCount(bool $condition = true): static
    {
        $this->showWordCount = $condition;

        return $this;
    }

    /**
     * Get the toolbar buttons array.
     */
    protected function getToolbarButtons(): array
    {
        if ($this->toolbarButtons instanceof Closure) {
            return ($this->toolbarButtons)();
        }

        return $this->toolbarButtons;
    }

    /**
     * Get the floating toolbars array.
     */
    protected function getFloatingToolbars(): array
    {
        if ($this->floatingToolbars instanceof Closure) {
            return ($this->floatingToolbars)();
        }

        return $this->floatingToolbars;
    }

    /**
     * Get the text colors array.
     */
    protected function getTextColors(): array
    {
        if ($this->textColors instanceof Closure) {
            return ($this->textColors)();
        }

        return $this->textColors;
    }

    /**
     * Get the custom text colors array.
     */
    protected function getCustomTextColors(): array
    {
        if ($this->customTextColors instanceof Closure) {
            return ($this->customTextColors)();
        }

        return $this->customTextColors;
    }

    /**
     * Get the file attachments accepted types array.
     */
    protected function getFileAttachmentsAcceptedFileTypes(): array
    {
        if ($this->fileAttachmentsAcceptedFileTypes instanceof Closure) {
            return ($this->fileAttachmentsAcceptedFileTypes)();
        }

        return $this->fileAttachmentsAcceptedFileTypes;
    }

    /**
     * Get the custom blocks array.
     */
    protected function getCustomBlocks(): array
    {
        if ($this->customBlocks instanceof Closure) {
            return ($this->customBlocks)();
        }

        return $this->customBlocks;
    }

    /**
     * Get the merge tags array.
     */
    protected function getMergeTags(): array
    {
        if ($this->mergeTags instanceof Closure) {
            return ($this->mergeTags)();
        }

        return $this->mergeTags;
    }

    protected function getVueComponent(): string
    {
        return 'RichEditor';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'toolbarButtons' => $this->getToolbarButtons(),
            'minHeight' => $this->minHeight,
            'maxHeight' => $this->maxHeight,
            'json' => $this->json,
            'floatingToolbars' => $this->getFloatingToolbars(),
            'textColors' => $this->getTextColors(),
            'customTextColors' => $this->getCustomTextColors(),
            'fileAttachmentsDisk' => $this->fileAttachmentsDisk,
            'fileAttachmentsDirectory' => $this->fileAttachmentsDirectory,
            'fileAttachmentsVisibility' => $this->fileAttachmentsVisibility,
            'fileAttachmentsAcceptedFileTypes' => $this->getFileAttachmentsAcceptedFileTypes(),
            'fileAttachmentsMaxSize' => $this->fileAttachmentsMaxSize,
            'customBlocks' => $this->getCustomBlocks(),
            'mergeTags' => $this->getMergeTags(),
            'activePanel' => $this->activePanel,
            'showCharacterCount' => $this->showCharacterCount,
            'showWordCount' => $this->showWordCount,
        ], $this->getCommonFieldProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltRichEditor';
    }

    protected function getFlutterWidgetProps(): array
    {
        return array_merge([
            'toolbarButtons' => $this->getToolbarButtons(),
            'minHeight' => $this->minHeight,
            'maxHeight' => $this->maxHeight,
        ], $this->getCommonFlutterFieldProps());
    }
}
