<?php

namespace Laravilt\Forms\Components;

use Closure;

class Slider extends Field
{
    protected string $view = 'laravilt-forms::components.fields.slider';

    protected float|int $min = 0;

    protected float|int $max = 100;

    protected float|int $step = 1;

    protected array|Closure $marks = [];

    protected bool $showValue = true;

    /**
     * Set the minimum value.
     */
    public function min(float|int $min): static
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Set the maximum value.
     */
    public function max(float|int $max): static
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Set the step value.
     */
    public function step(float|int $step): static
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Set marks on the slider.
     */
    public function marks(array|Closure $marks): static
    {
        $this->marks = $marks;

        return $this;
    }

    /**
     * Show/hide the current value.
     */
    public function showValue(bool $condition = true): static
    {
        $this->showValue = $condition;

        return $this;
    }

    /**
     * Get the marks array.
     */
    protected function getMarks(): array
    {
        if ($this->marks instanceof Closure) {
            return ($this->marks)();
        }

        return $this->marks;
    }

    protected function getVueComponent(): string
    {
        return 'Slider';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'marks' => $this->getMarks(),
            'showValue' => $this->showValue,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? $this->min,
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltSlider';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'marks' => $this->getMarks(),
            'showValue' => $this->showValue,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? $this->min,
        ];
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'marks' => $this->getMarks(),
            'showValue' => $this->showValue,
        ]);
    }
}
