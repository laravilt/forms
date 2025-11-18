<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Color Picker Component
 *
 * A color picker field with support for:
 * - Hex colors
 * - RGB/RGBA colors
 * - Predefined color swatches
 * - Alpha channel (transparency)
 */
class ColorPicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.color-picker';

    /**
     * Whether to show alpha channel control.
     */
    protected bool|Closure $alpha = false;

    /**
     * Format for color value (hex, rgb, hsl).
     */
    protected string|Closure $format = 'hex';

    /**
     * Predefined color swatches.
     */
    protected array|Closure $swatches = [];

    /**
     * Whether to show the swatches.
     */
    protected bool|Closure $showSwatches = false;

    /**
     * Enable alpha channel control.
     */
    public function alpha(bool|Closure $condition = true): static
    {
        $this->alpha = $condition;

        return $this;
    }

    /**
     * Set the color format.
     */
    public function format(string|Closure $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Set predefined color swatches.
     */
    public function swatches(array|Closure $swatches): static
    {
        $this->swatches = $swatches;
        $this->showSwatches = true;

        return $this;
    }

    /**
     * Check if alpha channel is enabled.
     */
    public function hasAlpha(): bool
    {
        return $this->evaluate($this->alpha);
    }

    /**
     * Get the color format.
     */
    public function getFormat(): string
    {
        return $this->evaluate($this->format);
    }

    /**
     * Get the color swatches.
     */
    public function getSwatches(): array
    {
        return $this->evaluate($this->swatches);
    }

    /**
     * Check if swatches should be shown.
     */
    public function shouldShowSwatches(): bool
    {
        return $this->evaluate($this->showSwatches) && count($this->getSwatches()) > 0;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'alpha' => $this->hasAlpha(),
            'format' => $this->getFormat(),
            'swatches' => $this->getSwatches(),
            'showSwatches' => $this->shouldShowSwatches(),
        ]);
    }
}
