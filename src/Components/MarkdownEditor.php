<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Markdown Editor Component
 *
 * A markdown editor with support for:
 * - Live preview
 * - Syntax highlighting
 * - Toolbar shortcuts
 * - File uploads
 * - Split view (editor + preview)
 */
class MarkdownEditor extends Field
{
    protected string $view = 'laravilt-forms::components.fields.markdown-editor';

    /**
     * Whether to show live preview.
     */
    protected bool|Closure $livePreview = true;

    /**
     * Whether to use split view.
     */
    protected bool|Closure $splitView = false;

    /**
     * Whether to enable file uploads.
     */
    protected bool|Closure $uploads = false;

    /**
     * Toolbar buttons to show.
     */
    protected array|Closure|null $toolbarButtons = null;

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
     * Enable/disable live preview.
     */
    public function livePreview(bool|Closure $condition = true): static
    {
        $this->livePreview = $condition;

        return $this;
    }

    /**
     * Enable/disable split view.
     */
    public function splitView(bool|Closure $condition = true): static
    {
        $this->splitView = $condition;

        return $this;
    }

    /**
     * Enable/disable file uploads.
     */
    public function uploads(bool|Closure $condition = true): static
    {
        $this->uploads = $condition;

        return $this;
    }

    /**
     * Set custom toolbar buttons.
     */
    public function toolbarButtons(array|Closure $buttons): static
    {
        $this->toolbarButtons = $buttons;

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
     * Check if live preview is enabled.
     */
    public function hasLivePreview(): bool
    {
        return $this->evaluate($this->livePreview);
    }

    /**
     * Check if split view is enabled.
     */
    public function hasSplitView(): bool
    {
        return $this->evaluate($this->splitView);
    }

    /**
     * Check if uploads are enabled.
     */
    public function hasUploads(): bool
    {
        return $this->evaluate($this->uploads);
    }

    /**
     * Get toolbar buttons.
     */
    public function getToolbarButtons(): ?array
    {
        return $this->evaluate($this->toolbarButtons);
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
            'livePreview' => $this->hasLivePreview(),
            'splitView' => $this->hasSplitView(),
            'uploads' => $this->hasUploads(),
            'toolbarButtons' => $this->getToolbarButtons(),
            'minHeight' => $this->evaluate($this->minHeight),
            'maxHeight' => $this->evaluate($this->maxHeight),
            'showCharacterCount' => $this->shouldShowCharacterCount(),
            'showWordCount' => $this->shouldShowWordCount(),
        ]);
    }
}
