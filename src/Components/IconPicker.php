<?php

namespace Laravilt\Forms\Components;

use Closure;

class IconPicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.icon-picker';

    protected array|Closure $icons = [];

    protected bool|Closure $searchable = true;

    protected string|Closure|null $placeholder = null;

    protected int|Closure $gridColumns = 8;

    protected bool|Closure $showIconName = true;

    /**
     * Set the available icons.
     */
    public function icons(array|Closure $icons): static
    {
        $this->icons = $icons;

        return $this;
    }

    /**
     * Enable/disable search functionality.
     */
    public function searchable(bool|Closure $condition = true): static
    {
        $this->searchable = $condition;

        return $this;
    }

    /**
     * Set the placeholder text.
     */
    public function placeholder(string|Closure|null $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set the number of grid columns.
     */
    public function gridColumns(int|Closure $columns): static
    {
        $this->gridColumns = $columns;

        return $this;
    }

    /**
     * Show/hide icon name below each icon.
     */
    public function showIconName(bool|Closure $condition = true): static
    {
        $this->showIconName = $condition;

        return $this;
    }

    /**
     * Get the icons array.
     */
    protected function getIcons(): array
    {
        $icons = $this->evaluate($this->icons);

        // If no icons specified, return popular Lucide icons
        if (empty($icons)) {
            return $this->getDefaultIcons();
        }

        return is_array($icons) ? $icons : [];
    }

    /**
     * Get default popular Lucide icons.
     */
    protected function getDefaultIcons(): array
    {
        return [
            'home', 'user', 'users', 'settings', 'search', 'heart', 'star',
            'mail', 'phone', 'message-square', 'bell', 'calendar', 'clock',
            'map-pin', 'tag', 'folder', 'file', 'image', 'video', 'music',
            'download', 'upload', 'trash', 'edit', 'check', 'x', 'plus', 'minus',
            'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down',
            'arrow-right', 'arrow-left', 'arrow-up', 'arrow-down',
            'external-link', 'link', 'copy', 'share', 'bookmark', 'flag',
            'shield', 'lock', 'unlock', 'eye', 'eye-off', 'help-circle',
            'info', 'alert-circle', 'alert-triangle', 'check-circle', 'x-circle',
            'sun', 'moon', 'cloud', 'zap', 'droplet', 'flame',
            'shopping-cart', 'shopping-bag', 'credit-card', 'dollar-sign',
            'briefcase', 'layers', 'grid', 'list', 'menu', 'more-horizontal',
        ];
    }

    protected function getVueComponent(): string
    {
        return 'IconPicker';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'icons' => $this->getIcons(),
            'searchable' => $this->evaluate($this->searchable),
            'placeholder' => $this->evaluate($this->placeholder) ?? 'Select an icon...',
            'gridColumns' => $this->evaluate($this->gridColumns),
            'showIconName' => $this->evaluate($this->showIconName),
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState(),
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltIconPicker';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'icons' => $this->getIcons(),
            'searchable' => $this->evaluate($this->searchable),
            'placeholder' => $this->evaluate($this->placeholder) ?? 'Select an icon...',
            'gridColumns' => $this->evaluate($this->gridColumns),
            'showIconName' => $this->evaluate($this->showIconName),
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState(),
        ];
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'icons' => $this->getIcons(),
            'searchable' => $this->evaluate($this->searchable),
            'placeholder' => $this->evaluate($this->placeholder) ?? 'Select an icon...',
            'gridColumns' => $this->evaluate($this->gridColumns),
            'showIconName' => $this->evaluate($this->showIconName),
        ]);
    }
}
