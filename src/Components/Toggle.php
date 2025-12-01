<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Toggle Field
 *
 * A toggle switch (on/off) with support for:
 * - Custom on/off values
 * - On/off labels
 * - On/off colors
 * - Icon support
 */
class Toggle extends Field
{
    protected string $view = 'laravilt-forms::components.fields.toggle';

    protected mixed $onValue = true;

    protected mixed $offValue = false;

    protected string|Closure|null $onLabel = null;

    protected string|Closure|null $offLabel = null;

    protected ?string $onIcon = null;

    protected ?string $offIcon = null;

    protected string $onColor = 'primary';

    protected string $offColor = 'gray';

    /**
     * Set the value when toggle is on.
     */
    public function onValue(mixed $value): static
    {
        $this->onValue = $value;

        return $this;
    }

    /**
     * Get the on value.
     */
    public function getOnValue(): mixed
    {
        return $this->evaluate($this->onValue);
    }

    /**
     * Set the value when toggle is off.
     */
    public function offValue(mixed $value): static
    {
        $this->offValue = $value;

        return $this;
    }

    /**
     * Get the off value.
     */
    public function getOffValue(): mixed
    {
        return $this->evaluate($this->offValue);
    }

    /**
     * Set the label when toggle is on.
     */
    public function onLabel(string|Closure $label): static
    {
        $this->onLabel = $label;

        return $this;
    }

    /**
     * Get the on label.
     */
    public function getOnLabel(): ?string
    {
        return $this->evaluate($this->onLabel);
    }

    /**
     * Set the label when toggle is off.
     */
    public function offLabel(string|Closure $label): static
    {
        $this->offLabel = $label;

        return $this;
    }

    /**
     * Get the off label.
     */
    public function getOffLabel(): ?string
    {
        return $this->evaluate($this->offLabel);
    }

    /**
     * Set the icon when toggle is on.
     */
    public function onIcon(string $icon): static
    {
        $this->onIcon = $icon;

        return $this;
    }

    /**
     * Get the on icon.
     */
    public function getOnIcon(): ?string
    {
        return $this->evaluate($this->onIcon);
    }

    /**
     * Set the icon when toggle is off.
     */
    public function offIcon(string $icon): static
    {
        $this->offIcon = $icon;

        return $this;
    }

    /**
     * Get the off icon.
     */
    public function getOffIcon(): ?string
    {
        return $this->evaluate($this->offIcon);
    }

    /**
     * Set the color when toggle is on.
     */
    public function onColor(string $color): static
    {
        $this->onColor = $color;

        return $this;
    }

    /**
     * Get the on color.
     */
    public function getOnColor(): string
    {
        return $this->onColor;
    }

    /**
     * Set the color when toggle is off.
     */
    public function offColor(string $color): static
    {
        $this->offColor = $color;

        return $this;
    }

    /**
     * Get the off color.
     */
    public function getOffColor(): string
    {
        return $this->offColor;
    }

    /**
     * Check if toggle is on.
     */
    public function isOn(): bool
    {
        return $this->getValue() === $this->getOnValue();
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'onValue' => $this->getOnValue(),
            'offValue' => $this->getOffValue(),
            'onLabel' => $this->getOnLabel(),
            'offLabel' => $this->getOffLabel(),
            'onIcon' => $this->getOnIcon(),
            'offIcon' => $this->getOffIcon(),
            'onColor' => $this->getOnColor(),
            'offColor' => $this->getOffColor(),
            'isOn' => $this->isOn(),
            'isLive' => $this->isLive(),
            'isLazy' => $this->isLazy(),
            'liveDebounce' => $this->getLiveDebounce(),
        ]);
    }
}
