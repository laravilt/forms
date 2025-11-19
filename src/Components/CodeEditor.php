<?php

namespace Laravilt\Forms\Components;

use Laravilt\Forms\Components\CodeEditor\Language;

class CodeEditor extends Field
{
    protected string $view = 'laravilt-forms::components.fields.code-editor';

    protected Language|string $language = Language::JavaScript;

    protected string $theme = 'light';

    protected bool $lineNumbers = true;

    protected bool $readOnly = false;

    /**
     * Set the programming language.
     */
    public function language(Language|string $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Set dark theme.
     */
    public function darkTheme(): static
    {
        $this->theme = 'dark';

        return $this;
    }

    /**
     * Set light theme.
     */
    public function lightTheme(): static
    {
        $this->theme = 'light';

        return $this;
    }

    protected function getVueComponent(): string
    {
        return 'CodeEditor';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'language' => $this->language instanceof Language ? $this->language->value : $this->language,
            'theme' => $this->theme,
            'lineNumbers' => $this->lineNumbers,
            'readOnly' => $this->readOnly,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState(),
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltCodeEditor';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'language' => $this->language instanceof Language ? $this->language->value : $this->language,
            'theme' => $this->theme,
            'lineNumbers' => $this->lineNumbers,
            'readOnly' => $this->readOnly,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState(),
        ];
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'language' => $this->language instanceof Language ? $this->language->value : $this->language,
            'theme' => $this->theme,
            'lineNumbers' => $this->lineNumbers,
            'readOnly' => $this->readOnly,
        ]);
    }
}
