<?php

namespace Laravilt\Forms\View\Components;

use Illuminate\View\Component;

class FieldWrapper extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $field = null,
        public $id = null,
        public $name = null,
        public $label = null,
        public $helperText = null,
        public $hint = null,
        public $required = false,
        public $disabled = false,
        public $hidden = false,
        public $errors = null,
        public $columnSpan = 1,
        public $extraFieldWrapperAttributes = [],
        public $extraLabelAttributes = [],
        public $extraContentAttributes = [],
        public $hiddenLabel = false,
        public $labelSrOnly = false,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('laravilt-forms::components.field-wrapper');
    }
}
