<?php

namespace Laravilt\Forms\Components;

/**
 * Textarea Field
 *
 * A multi-line text input field with support for:
 * - Auto-resizing
 * - Row configuration
 * - Character/word count
 * - Max length enforcement
 */
class Textarea extends Field
{
    protected string $view = 'laravilt-forms::components.fields.textarea';

    protected ?int $rows = 3;

    protected ?int $minRows = null;

    protected ?int $maxRows = null;

    protected bool $autosize = false;

    protected ?int $maxLength = null;

    protected bool $showCharacterCount = false;

    protected bool $showWordCount = false;

    /**
     * Set the number of rows.
     */
    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the number of rows.
     */
    public function getRows(): ?int
    {
        return $this->rows;
    }

    /**
     * Set minimum rows for autosize.
     */
    public function minRows(int $rows): static
    {
        $this->minRows = $rows;
        $this->autosize = true;

        return $this;
    }

    /**
     * Get minimum rows.
     */
    public function getMinRows(): ?int
    {
        return $this->minRows;
    }

    /**
     * Set maximum rows for autosize.
     */
    public function maxRows(int $rows): static
    {
        $this->maxRows = $rows;
        $this->autosize = true;

        return $this;
    }

    /**
     * Get maximum rows.
     */
    public function getMaxRows(): ?int
    {
        return $this->maxRows;
    }

    /**
     * Enable auto-resizing based on content.
     */
    public function autosize(bool $condition = true): static
    {
        $this->autosize = $condition;

        return $this;
    }

    /**
     * Check if autosize is enabled.
     */
    public function shouldAutosize(): bool
    {
        return $this->autosize;
    }

    /**
     * Set maximum length.
     */
    public function maxLength(int $length): static
    {
        $this->maxLength = $length;

        return parent::maxLength($length);
    }

    /**
     * Get maximum length.
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * Show character count.
     */
    public function characterCount(bool $show = true): static
    {
        $this->showCharacterCount = $show;

        return $this;
    }

    /**
     * Show character count (alias).
     */
    public function showCharacterCount(bool $show = true): static
    {
        return $this->characterCount($show);
    }

    /**
     * Check if character count should be shown.
     */
    public function shouldShowCharacterCount(): bool
    {
        return $this->showCharacterCount;
    }

    /**
     * Show word count.
     */
    public function wordCount(bool $show = true): static
    {
        $this->showWordCount = $show;

        return $this;
    }

    /**
     * Show word count (alias).
     */
    public function showWordCount(bool $show = true): static
    {
        return $this->wordCount($show);
    }

    /**
     * Check if word count should be shown.
     */
    public function shouldShowWordCount(): bool
    {
        return $this->showWordCount;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'rows' => $this->getRows(),
            'minRows' => $this->getMinRows(),
            'maxRows' => $this->getMaxRows(),
            'autosize' => $this->shouldAutosize(),
            'maxLength' => $this->getMaxLength(),
            'showCharacterCount' => $this->shouldShowCharacterCount(),
            'showWordCount' => $this->shouldShowWordCount(),
        ]);
    }
}
