<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Rich Editor Component
 *
 * A WYSIWYG rich text editor with support for:
 * - Text formatting (bold, italic, underline)
 * - Headings and paragraphs
 * - Lists (ordered, unordered)
 * - Links and images
 * - Code blocks
 * - Tables
 * - Custom toolbar
 */
class RichEditor extends Field
{
    protected string $view = 'laravilt-forms::components.fields.rich-editor';

    /**
     * Toolbar buttons to show.
     */
    protected array|Closure|null $toolbarButtons = null;

    /**
     * Whether to disable certain features.
     */
    protected array|Closure $disabledFeatures = [];

    /**
     * Maximum file size for image uploads (KB).
     */
    protected int|Closure|null $maxImageSize = null;

    /**
     * Allowed image formats.
     */
    protected array|Closure $imageFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * Minimum height in pixels.
     */
    protected int|Closure|null $minHeight = null;

    /**
     * Maximum height in pixels.
     */
    protected int|Closure|null $maxHeight = null;

    /**
     * Whether to show character count.
     */
    protected bool|Closure $showCharacterCount = false;

    /**
     * Whether to show word count.
     */
    protected bool|Closure $showWordCount = false;

    /**
     * Set custom toolbar buttons.
     */
    public function toolbarButtons(array|Closure $buttons): static
    {
        $this->toolbarButtons = $buttons;

        return $this;
    }

    /**
     * Disable specific features.
     */
    public function disableFeatures(array|Closure $features): static
    {
        $this->disabledFeatures = $features;

        return $this;
    }

    /**
     * Disable image uploads.
     */
    public function disableImages(): static
    {
        return $this->disableFeatures(['image']);
    }

    /**
     * Disable links.
     */
    public function disableLinks(): static
    {
        return $this->disableFeatures(['link']);
    }

    /**
     * Set maximum image upload size.
     */
    public function maxImageSize(int|Closure $size): static
    {
        $this->maxImageSize = $size;

        return $this;
    }

    /**
     * Set allowed image formats.
     */
    public function imageFormats(array|Closure $formats): static
    {
        $this->imageFormats = $formats;

        return $this;
    }

    /**
     * Set minimum height.
     */
    public function minHeight(int|Closure $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    /**
     * Set maximum height.
     */
    public function maxHeight(int|Closure $height): static
    {
        $this->maxHeight = $height;

        return $this;
    }

    /**
     * Show character count.
     */
    public function showCharacterCount(bool|Closure $condition = true): static
    {
        $this->showCharacterCount = $condition;

        return $this;
    }

    /**
     * Show word count.
     */
    public function showWordCount(bool|Closure $condition = true): static
    {
        $this->showWordCount = $condition;

        return $this;
    }

    /**
     * Get toolbar buttons.
     */
    public function getToolbarButtons(): ?array
    {
        return $this->evaluate($this->toolbarButtons);
    }

    /**
     * Get disabled features.
     */
    public function getDisabledFeatures(): array
    {
        return $this->evaluate($this->disabledFeatures);
    }

    /**
     * Get allowed image formats.
     */
    public function getImageFormats(): array
    {
        return $this->evaluate($this->imageFormats);
    }

    /**
     * Check if character count should be shown.
     */
    public function shouldShowCharacterCount(): bool
    {
        return $this->evaluate($this->showCharacterCount);
    }

    /**
     * Check if word count should be shown.
     */
    public function shouldShowWordCount(): bool
    {
        return $this->evaluate($this->showWordCount);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'toolbarButtons' => $this->getToolbarButtons(),
            'disabledFeatures' => $this->getDisabledFeatures(),
            'maxImageSize' => $this->evaluate($this->maxImageSize),
            'imageFormats' => $this->getImageFormats(),
            'minHeight' => $this->evaluate($this->minHeight),
            'maxHeight' => $this->evaluate($this->maxHeight),
            'showCharacterCount' => $this->shouldShowCharacterCount(),
            'showWordCount' => $this->shouldShowWordCount(),
        ]);
    }
}
