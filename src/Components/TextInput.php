<?php

namespace Laravilt\Forms\Components;

/**
 * Text Input Field
 *
 * A single-line text input field with support for:
 * - Various input types (text, email, password, tel, url, etc.)
 * - Prefix and suffix icons/text
 * - Prefix and suffix actions
 * - Input masking
 * - Character count
 */
class TextInput extends Field
{
    protected string $view = 'laravilt-forms::components.fields.text-input';

    protected string $type = 'text';

    protected ?string $prefixIcon = null;

    protected ?string $suffixIcon = null;

    protected ?string $prefixText = null;

    protected ?string $suffixText = null;

    protected ?int $minLength = null;

    protected ?int $maxLength = null;

    protected ?string $pattern = null;

    protected ?string $mask = null;

    protected bool $showCharacterCount = false;

    /**
     * Set the input type.
     */
    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the input type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set input type to email.
     */
    public function email(): static
    {
        $this->type = 'email';
        $this->autocomplete = 'email';

        return parent::email();
    }

    /**
     * Set input type to password.
     */
    public function password(): static
    {
        $this->type = 'password';
        $this->autocomplete = 'current-password';

        return $this;
    }

    /**
     * Set input type to tel.
     */
    public function tel(): static
    {
        $this->type = 'tel';
        $this->autocomplete = 'tel';

        return $this;
    }

    /**
     * Set input type to url.
     */
    public function url(): static
    {
        $this->type = 'url';
        $this->autocomplete = 'url';

        return parent::url();
    }

    /**
     * Set input type to search.
     */
    public function search(): static
    {
        $this->type = 'search';

        return $this;
    }

    /**
     * Set prefix icon.
     */
    public function prefixIcon(string $icon): static
    {
        $this->prefixIcon = $icon;

        return $this;
    }

    /**
     * Get prefix icon.
     */
    public function getPrefixIcon(): ?string
    {
        return $this->evaluate($this->prefixIcon);
    }

    /**
     * Set suffix icon.
     */
    public function suffixIcon(string $icon): static
    {
        $this->suffixIcon = $icon;

        return $this;
    }

    /**
     * Get suffix icon.
     */
    public function getSuffixIcon(): ?string
    {
        return $this->evaluate($this->suffixIcon);
    }

    /**
     * Set prefix text.
     */
    public function prefix(string $text): static
    {
        $this->prefixText = $text;

        return $this;
    }

    /**
     * Get prefix text.
     */
    public function getPrefixText(): ?string
    {
        return $this->evaluate($this->prefixText);
    }

    /**
     * Get prefix (alias for getPrefixText).
     */
    public function getPrefix(): ?string
    {
        return $this->getPrefixText();
    }

    /**
     * Set suffix text.
     */
    public function suffix(string $text): static
    {
        $this->suffixText = $text;

        return $this;
    }

    /**
     * Get suffix text.
     */
    public function getSuffixText(): ?string
    {
        return $this->evaluate($this->suffixText);
    }

    /**
     * Get suffix (alias for getSuffixText).
     */
    public function getSuffix(): ?string
    {
        return $this->getSuffixText();
    }

    /**
     * Set minimum length.
     */
    public function minLength(int $length): static
    {
        $this->minLength = $length;

        return parent::minLength($length);
    }

    /**
     * Get minimum length.
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
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
     * Set pattern validation.
     */
    public function pattern(string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern.
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * Set input mask.
     */
    public function mask(string $mask): static
    {
        $this->mask = $mask;

        return $this;
    }

    /**
     * Get input mask.
     */
    public function getMask(): ?string
    {
        return $this->mask;
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
     * Check if character count should be shown.
     */
    public function shouldShowCharacterCount(): bool
    {
        return $this->showCharacterCount;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'type' => $this->getType(),
            'prefixIcon' => $this->getPrefixIcon(),
            'suffixIcon' => $this->getSuffixIcon(),
            'prefixText' => $this->getPrefixText(),
            'suffixText' => $this->getSuffixText(),
            'minLength' => $this->getMinLength(),
            'maxLength' => $this->getMaxLength(),
            'pattern' => $this->getPattern(),
            'mask' => $this->getMask(),
            'showCharacterCount' => $this->shouldShowCharacterCount(),
            'isLive' => $this->isLive(),
            'isLazy' => $this->isLazy(),
            'liveDebounce' => $this->getLiveDebounce(),
        ]);
    }
}
