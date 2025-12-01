<?php

namespace Laravilt\Forms\Concerns;

trait CanBeReactive
{
    /**
     * Whether the field should react to changes immediately (live).
     */
    protected bool $isLive = false;

    /**
     * Whether the field should react to changes with debounce (lazy).
     */
    protected bool $isLazy = false;

    /**
     * Debounce delay in milliseconds for reactive updates.
     */
    protected int $liveDebounce = 500;

    /**
     * Make the field reactive (live) - updates trigger immediate re-evaluation.
     *
     * @param  bool  $condition  Whether to enable live updates
     * @param  int  $debounce  Debounce delay in milliseconds
     */
    public function live(bool $condition = true, int $debounce = 0): static
    {
        $this->isLive = $condition;
        $this->liveDebounce = $debounce;

        return $this;
    }

    /**
     * Make the field reactive with debounce (lazy) - updates trigger debounced re-evaluation.
     *
     * @param  bool  $condition  Whether to enable lazy updates
     * @param  int  $debounce  Debounce delay in milliseconds (default: 500ms)
     */
    public function lazy(bool $condition = true, int $debounce = 500): static
    {
        $this->isLazy = $condition;
        $this->liveDebounce = $debounce;

        return $this;
    }

    /**
     * Check if the field is reactive (live).
     */
    public function isLive(): bool
    {
        return $this->isLive;
    }

    /**
     * Check if the field is reactive with debounce (lazy).
     */
    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    /**
     * Get the debounce delay for reactive updates.
     */
    public function getLiveDebounce(): int
    {
        return $this->liveDebounce;
    }
}
