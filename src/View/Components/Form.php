<?php

namespace Laravilt\Forms\View\Components;

use Illuminate\View\Component;

class Form extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $schema = [],
        public $columns = 1,
        public $action = null,
        public $method = 'POST',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('laravilt-forms::components.form');
    }
}
