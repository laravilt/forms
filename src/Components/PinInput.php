<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Pin Input Component
 *
 * A pin/OTP input field with individual character inputs.
 * Supports:
 * - Variable length (number of digits)
 * - Mask option for sensitive input
 * - One-time password mode
 * - Auto-focus and auto-submit
 */
class PinInput extends Field
{
    protected string $view = 'laravilt-forms::components.fields.pin-input';

    protected int|Closure $length = 4;

    protected bool|Closure $mask = false;

    protected bool|Closure $otp = false;

    protected string|Closure $type = 'numeric';

    /**
     * Set the number of input fields.
     */
    public function length(int|Closure $length): static
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get the number of input fields.
     */
    public function getLength(): int
    {
        return $this->evaluate($this->length);
    }

    /**
     * Enable masking of input (for passwords).
     */
    public function mask(bool|Closure $mask = true): static
    {
        $this->mask = $mask;

        return $this;
    }

    /**
     * Get mask state.
     */
    public function getMask(): bool
    {
        return $this->evaluate($this->mask);
    }

    /**
     * Mark this as a one-time password input.
     */
    public function otp(bool|Closure $otp = true): static
    {
        $this->otp = $otp;

        return $this;
    }

    /**
     * Get OTP state.
     */
    public function getOtp(): bool
    {
        return $this->evaluate($this->otp);
    }

    /**
     * Set input type (numeric, alpha, alphanumeric).
     */
    public function type(string|Closure $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get input type.
     */
    public function getType(): string
    {
        return $this->evaluate($this->type);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'length' => $this->getLength(),
            'mask' => $this->getMask(),
            'otp' => $this->getOtp(),
            'type' => $this->getType(),
        ]);
    }
}
