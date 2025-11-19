<?php

namespace Laravilt\Forms\Components;

use Closure;

class MarkdownEditor extends Field
{
    protected string $view = 'laravilt-forms::components.fields.markdown-editor';

    protected bool $preview = true;

    protected bool $showCharacterCount = false;

    protected bool $showWordCount = false;

    protected bool|Closure $fileAttachmentsEnabled = false;

    protected string|Closure|null $fileAttachmentsDisk = null;

    protected string|Closure|null $fileAttachmentsDirectory = null;

    protected array|Closure $fileAttachmentsAcceptedFileTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

    protected int|Closure $fileAttachmentsMaxSize = 12288; // 12 MB in KB

    protected array|Closure $toolbarButtons = [
        ['bold', 'italic', 'strike', 'link'],
        ['heading'],
        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
        ['table', 'attachFiles'],
        ['undo', 'redo'],
    ];

    /**
     * Enable/disable preview mode.
     */
    public function preview(bool $condition = true): static
    {
        $this->preview = $condition;

        return $this;
    }

    /**
     * Customize the toolbar buttons.
     * Each nested array represents a group of buttons.
     */
    public function toolbarButtons(array|Closure $buttons): static
    {
        $this->toolbarButtons = $buttons;

        return $this;
    }

    /**
     * Disable all toolbar buttons.
     */
    public function disableAllToolbarButtons(): static
    {
        $this->toolbarButtons = [];

        return $this;
    }

    /**
     * Enable file attachments (images).
     */
    public function fileAttachments(bool|Closure $condition = true): static
    {
        $this->fileAttachmentsEnabled = $condition;

        return $this;
    }

    /**
     * Set the disk for file attachments.
     */
    public function fileAttachmentsDisk(string|Closure $disk): static
    {
        $this->fileAttachmentsDisk = $disk;
        $this->fileAttachmentsEnabled = true;

        return $this;
    }

    /**
     * Set the directory for file attachments.
     */
    public function fileAttachmentsDirectory(string|Closure $directory): static
    {
        $this->fileAttachmentsDirectory = $directory;
        $this->fileAttachmentsEnabled = true;

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
     * Set maximum file size for attachments in kilobytes.
     */
    public function fileAttachmentsMaxSize(int|Closure $size): static
    {
        $this->fileAttachmentsMaxSize = $size;

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

    protected function getVueComponent(): string
    {
        return 'MarkdownEditor';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'placeholder' => $this->placeholder,
            'preview' => $this->preview,
            'showCharacterCount' => $this->showCharacterCount,
            'showWordCount' => $this->showWordCount,
            'toolbarButtons' => $this->evaluate($this->toolbarButtons),
            'fileAttachmentsEnabled' => $this->evaluate($this->fileAttachmentsEnabled),
            'fileAttachmentsDisk' => $this->evaluate($this->fileAttachmentsDisk) ?? 'public',
            'fileAttachmentsDirectory' => $this->evaluate($this->fileAttachmentsDirectory) ?? 'attachments',
            'fileAttachmentsAcceptedFileTypes' => $this->evaluate($this->fileAttachmentsAcceptedFileTypes),
            'fileAttachmentsMaxSize' => $this->evaluate($this->fileAttachmentsMaxSize),
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState(),
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltMarkdownEditor';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'placeholder' => $this->placeholder,
            'preview' => $this->preview,
            'toolbarButtons' => $this->evaluate($this->toolbarButtons),
            'fileAttachmentsEnabled' => $this->evaluate($this->fileAttachmentsEnabled),
            'fileAttachmentsDisk' => $this->evaluate($this->fileAttachmentsDisk) ?? 'public',
            'fileAttachmentsDirectory' => $this->evaluate($this->fileAttachmentsDirectory) ?? 'attachments',
            'fileAttachmentsAcceptedFileTypes' => $this->evaluate($this->fileAttachmentsAcceptedFileTypes),
            'fileAttachmentsMaxSize' => $this->evaluate($this->fileAttachmentsMaxSize),
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState(),
        ];
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'preview' => $this->preview,
            'toolbarButtons' => $this->evaluate($this->toolbarButtons),
            'fileAttachmentsEnabled' => $this->evaluate($this->fileAttachmentsEnabled),
            'fileAttachmentsDisk' => $this->evaluate($this->fileAttachmentsDisk) ?? 'public',
            'fileAttachmentsDirectory' => $this->evaluate($this->fileAttachmentsDirectory) ?? 'attachments',
            'fileAttachmentsAcceptedFileTypes' => $this->evaluate($this->fileAttachmentsAcceptedFileTypes),
            'fileAttachmentsMaxSize' => $this->evaluate($this->fileAttachmentsMaxSize),
        ]);
    }
}
