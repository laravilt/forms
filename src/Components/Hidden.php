<?php

namespace Laravilt\Forms\Components;

/**
 * Hidden Field
 *
 * A hidden input field that stores data but is not visible to users.
 */
class Hidden extends Field
{
    protected string $view = 'laravilt-forms::components.fields.hidden';

    /**
     * Set up the component.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Hidden fields are always hidden
        $this->hidden();
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'type' => 'hidden',
        ]);
    }
}
