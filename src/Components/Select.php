<?php

namespace Laravilt\Forms\Components;

use Closure;
use Illuminate\Support\Collection;
use Laravilt\Panel\Facades\Panel;
use Laravilt\Support\Utilities\Get;
use Laravilt\Support\Utilities\Set;

class Select extends Field
{
    protected string $view = 'laravilt-forms::components.fields.select';

    /**
     * @var array<string|int, string>|Closure
     */
    protected array|Closure $options = [];

    protected bool $searchable = false;

    protected bool $multiple = false;

    protected bool $native = true;

    protected ?string $relationship = null;

    protected ?string $titleAttribute = null;

    protected bool $preload = false;

    protected ?string $model = null;

    /**
     * The form's model class (set by parent Form).
     *
     * @var class-string|null
     */
    protected ?string $formModelClass = null;

    /**
     * The resource slug (set by parent Schema/Resource).
     */
    protected ?string $resourceSlug = null;

    /**
     * The relation manager class (for Select fields inside relation managers).
     */
    protected ?string $relationManagerClass = null;

    /**
     * Whether there are more options available (set when closure results are limited).
     */
    protected bool $hasMoreOptions = false;

    protected ?string $loadingMessage = null;

    protected ?string $noSearchResultsMessage = null;

    protected ?string $searchPrompt = null;

    protected ?string $searchingMessage = null;

    protected int $searchDebounce = 1000;

    /**
     * @var array<int, string>
     */
    protected array $searchableColumns = [];

    protected ?Closure $getSearchResultsUsing = null;

    protected ?Closure $getOptionLabelUsing = null;

    protected ?Closure $getOptionLabelsUsing = null;

    protected ?Closure $getOptionLabelFromRecordUsing = null;

    protected bool $optionsAreGrouped = false;

    protected ?Closure $modifyQueryUsing = null;

    /**
     * @var array<string, mixed>|Closure
     */
    protected array|Closure $pivotData = [];

    protected ?Closure $createOptionUsing = null;

    /**
     * @var array<int, mixed>
     */
    protected array $createOptionForm = [];

    protected ?Closure $updateOptionUsing = null;

    /**
     * @var array<int, mixed>
     */
    protected array $editOptionForm = [];

    protected bool $allowHtml = false;

    protected bool $wrapOptionLabels = true;

    protected bool $selectablePlaceholder = true;

    protected ?Closure $disableOptionWhen = null;

    protected int $optionsLimit = 50;

    protected ?int $minItems = null;

    protected ?int $maxItems = null;

    /**
     * @var array<int, string>
     */
    protected array $dependsOn = [];

    protected ?string $optionsUrl = null;

    /**
     * Whether the field should react to changes immediately (live) or with debounce (lazy).
     */
    protected bool $isLive = false;

    protected bool $isLazy = false;

    protected int $liveDebounce = 500;

    /**
     * Set the select options.
     *
     * @param  array<string|int, string>|Closure  $options
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

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
     * Make this select depend on other fields for dynamic options.
     *
     * @param  array<int, string>|string  $fields  Field names to depend on
     */
    public function dependsOn(array|string $fields): static
    {
        $this->dependsOn = is_array($fields) ? $fields : [$fields];

        return $this;
    }

    /**
     * Set a URL endpoint to load options dynamically.
     */
    public function optionsUrl(string $url): static
    {
        $this->optionsUrl = $url;

        return $this;
    }

    /**
     * Load options from a relationship.
     *
     * @param  string  $relationshipName  The name of the relationship method
     * @param  string  $titleAttribute  The attribute to display as the option label
     * @param  Closure|null  $modifyQueryUsing  Closure to modify the relationship query
     */
    public function relationship(
        string $relationshipName,
        string $titleAttribute = 'name',
        ?Closure $modifyQueryUsing = null
    ): static {
        $this->relationship = $relationshipName;
        $this->titleAttribute = $titleAttribute;

        if ($modifyQueryUsing !== null) {
            $this->modifyQueryUsing = $modifyQueryUsing;
        }

        return $this;
    }

    /**
     * Preload all relationship options (instead of lazy loading).
     */
    public function preload(bool $condition = true): static
    {
        $this->preload = $condition;

        return $this;
    }

    /**
     * Make the select searchable.
     *
     * @param  bool|array<int, string>  $condition  True/false or array of columns to search
     */
    public function searchable(bool|array $condition = true): static
    {
        if (is_array($condition)) {
            $this->searchable = true;
            $this->searchableColumns = $condition;
            $this->native = false;
        } else {
            $this->searchable = $condition;

            if ($condition) {
                $this->native = false;
            }
        }

        return $this;
    }

    /**
     * Allow multiple selections.
     */
    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    /**
     * Use native select element (no custom dropdown).
     */
    public function native(bool $condition = true): static
    {
        $this->native = $condition;

        return $this;
    }

    /**
     * Set custom loading message.
     */
    public function loadingMessage(?string $message): static
    {
        $this->loadingMessage = $message;

        return $this;
    }

    /**
     * Set custom no search results message.
     */
    public function noSearchResultsMessage(?string $message): static
    {
        $this->noSearchResultsMessage = $message;

        return $this;
    }

    /**
     * Set custom search prompt.
     */
    public function searchPrompt(?string $message): static
    {
        $this->searchPrompt = $message;

        return $this;
    }

    /**
     * Set custom searching message.
     */
    public function searchingMessage(?string $message): static
    {
        $this->searchingMessage = $message;

        return $this;
    }

    /**
     * Set search debounce in milliseconds.
     */
    public function searchDebounce(int $milliseconds): static
    {
        $this->searchDebounce = $milliseconds;

        return $this;
    }

    /**
     * Set custom search results callback.
     */
    public function getSearchResultsUsing(?Closure $callback): static
    {
        $this->getSearchResultsUsing = $callback;

        return $this;
    }

    /**
     * Set custom option label callback (for single select).
     */
    public function getOptionLabelUsing(?Closure $callback): static
    {
        $this->getOptionLabelUsing = $callback;

        return $this;
    }

    /**
     * Set custom option labels callback (for multi-select).
     */
    public function getOptionLabelsUsing(?Closure $callback): static
    {
        $this->getOptionLabelsUsing = $callback;

        return $this;
    }

    /**
     * Set custom option label from record callback.
     */
    public function getOptionLabelFromRecordUsing(?Closure $callback): static
    {
        $this->getOptionLabelFromRecordUsing = $callback;

        return $this;
    }

    /**
     * Set pivot data for many-to-many relationships.
     *
     * @param  array<string, mixed>|Closure  $data
     */
    public function pivotData(array|Closure $data): static
    {
        $this->pivotData = $data;

        return $this;
    }

    /**
     * Set create option form schema.
     *
     * @param  array<int, mixed>  $schema
     */
    public function createOptionForm(array $schema): static
    {
        $this->createOptionForm = $schema;

        return $this;
    }

    /**
     * Set custom create option callback.
     */
    public function createOptionUsing(?Closure $callback): static
    {
        $this->createOptionUsing = $callback;

        return $this;
    }

    /**
     * Set edit option form schema.
     *
     * @param  array<int, mixed>  $schema
     */
    public function editOptionForm(array $schema): static
    {
        $this->editOptionForm = $schema;

        return $this;
    }

    /**
     * Set custom update option callback.
     */
    public function updateOptionUsing(?Closure $callback): static
    {
        $this->updateOptionUsing = $callback;

        return $this;
    }

    /**
     * Allow HTML in option labels.
     */
    public function allowHtml(bool $condition = true): static
    {
        $this->allowHtml = $condition;

        return $this;
    }

    /**
     * Wrap option labels instead of truncating.
     */
    public function wrapOptionLabels(bool $condition = true): static
    {
        $this->wrapOptionLabels = $condition;

        return $this;
    }

    /**
     * Allow placeholder to be selectable.
     */
    public function selectablePlaceholder(bool $condition = true): static
    {
        $this->selectablePlaceholder = $condition;

        return $this;
    }

    /**
     * Disable specific options based on condition.
     */
    public function disableOptionWhen(?Closure $callback): static
    {
        $this->disableOptionWhen = $callback;

        return $this;
    }

    /**
     * Set options limit for searchable selects.
     */
    public function optionsLimit(int $limit): static
    {
        $this->optionsLimit = $limit;

        return $this;
    }

    /**
     * Set minimum number of items for multi-select.
     */
    public function minItems(?int $count): static
    {
        $this->minItems = $count;

        return $this;
    }

    /**
     * Set maximum number of items for multi-select.
     */
    public function maxItems(?int $count): static
    {
        $this->maxItems = $count;

        return $this;
    }

    /**
     * Configure as a boolean select.
     */
    public function boolean(
        ?string $trueLabel = 'Yes',
        ?string $falseLabel = 'No',
        ?string $placeholder = 'Select an option'
    ): static {
        $this->options = [
            1 => $trueLabel,
            0 => $falseLabel,
        ];

        if ($placeholder) {
            $this->placeholder = $placeholder;
        }

        return $this;
    }

    /**
     * Get the relationship name.
     */
    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    /**
     * Get the title attribute.
     */
    public function getTitleAttribute(): ?string
    {
        return $this->titleAttribute;
    }

    /**
     * Get pivot data for many-to-many relationships.
     *
     * @return array<string, mixed>
     */
    public function getPivotData(): array
    {
        if ($this->pivotData instanceof Closure) {
            return ($this->pivotData)();
        }

        return $this->pivotData;
    }

    /**
     * Get searchable columns.
     *
     * @return array<int, string>
     */
    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    /**
     * Check if has custom search callback.
     */
    public function hasCustomSearch(): bool
    {
        return $this->getSearchResultsUsing !== null;
    }

    /**
     * Execute custom search callback.
     *
     * @return array<string|int, string>
     */
    public function executeCustomSearch(string $search): array
    {
        if ($this->getSearchResultsUsing) {
            return ($this->getSearchResultsUsing)($search);
        }

        return [];
    }

    /**
     * Get option label for a value (single select).
     */
    public function getOptionLabel(mixed $value): ?string
    {
        if ($this->getOptionLabelUsing) {
            return ($this->getOptionLabelUsing)($value);
        }

        return null;
    }

    /**
     * Get option labels for values (multi-select).
     *
     * @param  array<int, mixed>  $values
     * @return array<string|int, string>
     */
    public function getOptionLabels(array $values): array
    {
        if ($this->getOptionLabelsUsing) {
            return ($this->getOptionLabelsUsing)($values);
        }

        return [];
    }

    /**
     * Check if an option should be disabled.
     */
    public function isOptionDisabled(mixed $value): bool
    {
        if ($this->disableOptionWhen) {
            return ($this->disableOptionWhen)($value);
        }

        return false;
    }

    /**
     * Get the options array (for non-relationship selects).
     *
     * @return array<string|int, string>
     */
    public function getOptions(): array
    {
        if ($this->options instanceof Closure) {
            return ($this->options)();
        }

        return $this->options;
    }

    /**
     * Get the current selected value(s) as an array.
     *
     * @return array<int, string|int>
     */
    protected function getSelectedValues(): array
    {
        $state = $this->getState();

        if ($state === null || $state === '') {
            return [];
        }

        if (is_array($state)) {
            return array_filter($state, fn ($v) => $v !== null && $v !== '');
        }

        return [(string) $state];
    }

    /**
     * Get the resolved options array.
     *
     * @return array<string|int, string>
     */
    protected function getResolvedOptions(): array
    {
        // If relationship is set, load options from the related model
        if ($this->relationship) {
            return $this->loadRelationshipOptions();
        }

        if ($this->options instanceof Closure) {
            // Check if closure requires Get/Set parameters
            $reflection = new \ReflectionFunction($this->options);
            $parameters = $reflection->getParameters();

            // If closure has parameters (Get/Set), evaluate with current form data
            if (count($parameters) > 0) {
                // Use evaluation context data (set by evaluationContext()) first,
                // fall back to request formData for backward compatibility
                $formData = ! empty($this->evaluationData)
                    ? $this->evaluationData
                    : request()->input('formData', []);

                // Create Get and Set utilities
                $get = new Get($formData);
                $set = new Set($formData);

                // Evaluate the closure with Get and Set
                $evaluatedOptions = ($this->options)($get, $set);

                // If the evaluation returns null or non-array, return empty array
                if (! is_array($evaluatedOptions)) {
                    return [];
                }

                return $evaluatedOptions;
            }

            // Otherwise, evaluate the closure (simple closure with no params)
            $result = ($this->options)();

            // Convert Collection to array if needed
            if ($result instanceof Collection) {
                $result = $result->all();
            }

            if (! is_array($result)) {
                return [];
            }

            // If result has many items, limit to optionsLimit (default 50)
            // Frontend will handle load more / search
            $limit = $this->optionsLimit ?? 50;
            if (count($result) > $limit) {
                $this->hasMoreOptions = true;

                // Get current selected value(s) to ensure they're included
                $selectedValues = $this->getSelectedValues();

                // Take initial limited options
                $limitedOptions = array_slice($result, 0, $limit, true);

                // Ensure selected values are included in the limited options
                if (! empty($selectedValues)) {
                    foreach ($selectedValues as $selectedValue) {
                        // If selected value exists in full result but not in limited options, add it
                        if (isset($result[$selectedValue]) && ! isset($limitedOptions[$selectedValue])) {
                            $limitedOptions[$selectedValue] = $result[$selectedValue];
                        }
                    }
                }

                return $limitedOptions;
            }

            return $result;
        }

        // Convert Collection to array if needed
        if ($this->options instanceof Collection) {
            return $this->options->all();
        }

        return $this->options;
    }

    /**
     * Load options from the relationship.
     *
     * @return array<string|int, string>
     */
    protected function loadRelationshipOptions(): array
    {
        // Get the model class from the component's model or parent form
        $modelClass = $this->getModelClass();

        if (! $modelClass) {
            return [];
        }

        $relationshipMethod = $this->relationship;
        $titleAttribute = $this->titleAttribute ?? 'name';

        // Create a new model instance to access the relationship
        $modelInstance = new $modelClass;

        // Get the relationship instance
        if (! method_exists($modelInstance, $relationshipMethod)) {
            return [];
        }

        $relation = $modelInstance->{$relationshipMethod}();
        $relatedModel = $relation->getRelated();

        // Query all related records
        $query = $relatedModel::query();

        // Apply custom query modifications if provided
        if ($this->modifyQueryUsing) {
            $query = ($this->modifyQueryUsing)($query);
        }

        $records = $query->get();

        // Build options array [id => title]
        $options = [];
        foreach ($records as $record) {
            // Use custom label callback if provided
            if ($this->getOptionLabelFromRecordUsing) {
                $options[$record->id] = ($this->getOptionLabelFromRecordUsing)($record);
            } else {
                $options[$record->id] = $record->{$titleAttribute};
            }
        }

        return $options;
    }

    /**
     * Load limited options from the relationship (for initial display).
     *
     * @param  int  $limit  Maximum number of options to load
     * @return array<string|int, string>
     */
    protected function loadRelationshipOptionsLimited(int $limit = 50): array
    {
        $modelClass = $this->getModelClass();

        if (! $modelClass) {
            return [];
        }

        $relationshipMethod = $this->relationship;
        $titleAttribute = $this->titleAttribute ?? 'name';

        // Create a new model instance to access the relationship
        $modelInstance = new $modelClass;

        if (! method_exists($modelInstance, $relationshipMethod)) {
            return [];
        }

        $relation = $modelInstance->{$relationshipMethod}();
        $relatedModel = $relation->getRelated();

        // Query limited records
        $query = $relatedModel::query();

        // Apply custom query modifications if provided
        if ($this->modifyQueryUsing) {
            $query = ($this->modifyQueryUsing)($query);
        }

        $records = $query->limit($limit)->get();

        // Ensure selected value(s) are always included in options
        $selectedValues = $this->getSelectedValues();
        if (! empty($selectedValues)) {
            $loadedIds = $records->pluck($relatedModel->getKeyName())->toArray();
            $missingIds = array_diff($selectedValues, array_map('strval', $loadedIds));

            if (! empty($missingIds)) {
                // Fetch missing selected records
                $missingRecords = $relatedModel::whereIn($relatedModel->getKeyName(), $missingIds)->get();
                $records = $records->merge($missingRecords);
            }
        }

        // Build options array [id => title]
        $options = [];
        foreach ($records as $record) {
            // Use custom label callback if provided
            if ($this->getOptionLabelFromRecordUsing) {
                $options[$record->getKey()] = ($this->getOptionLabelFromRecordUsing)($record);
            } else {
                // Build label - enhance for email fields
                $label = $record->{$titleAttribute};
                if ($titleAttribute === 'email') {
                    if ($record->first_name || $record->last_name) {
                        $label = trim($record->first_name.' '.$record->last_name).' ('.$record->email.')';
                    } elseif ($record->name) {
                        $label = $record->name.' ('.$record->email.')';
                    }
                }
                $options[$record->getKey()] = $label;
            }
        }

        return $options;
    }

    /**
     * Set the form's model class (called by parent Form).
     *
     * @param  class-string  $model
     */
    public function formModel(string $model): static
    {
        $this->formModelClass = $model;

        return $this;
    }

    /**
     * Set the resource slug (called by parent Schema/Resource).
     */
    public function resourceSlug(string $slug): static
    {
        $this->resourceSlug = $slug;

        return $this;
    }

    /**
     * Set the relation manager class (for Select fields inside relation managers).
     */
    public function relationManagerClass(string $class): static
    {
        $this->relationManagerClass = $class;

        return $this;
    }

    /**
     * Get the model class name for this component.
     */
    protected function getModelClass(): ?string
    {
        // Priority: explicit model > form model class > panel resource model > parent model
        if ($this->model) {
            return $this->model;
        }

        if ($this->formModelClass) {
            return $this->formModelClass;
        }

        // Try to get model from Panel's current resource context
        $resourceModel = $this->getModelFromPanelResource();
        if ($resourceModel) {
            return $resourceModel;
        }

        return $this->getParentModel();
    }

    /**
     * Try to get model class from the Panel's current resource context.
     */
    protected function getModelFromPanelResource(): ?string
    {
        try {
            // Get current panel
            $panel = Panel::getCurrent();
            if (! $panel) {
                return null;
            }

            // Get current resource from URL
            $currentPath = request()->path();
            $pathParts = explode('/', $currentPath);

            // Find resource by matching URL slug
            foreach ($panel->getResources() as $resourceClass) {
                $slug = $resourceClass::getSlug();
                if (in_array($slug, $pathParts)) {
                    $model = $resourceClass::getModel();
                    if ($model) {
                        return $model;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail - model lookup is optional
        }

        return null;
    }

    /**
     * Get model class from parent container (form/schema).
     */
    protected function getParentModel(): ?string
    {
        // Try to infer from evaluation record if available
        if ($this->evaluationRecord && is_object($this->evaluationRecord)) {
            return get_class($this->evaluationRecord);
        }

        return null;
    }

    /**
     * Evaluate closure-based options with Get/Set utilities.
     *
     * This allows Filament-style closure evaluation:
     * ->options(fn(Get $get, Set $set) => $get('country_id') ? State::where(...)->pluck(...) : [])
     */
    public function evaluateOptions($get, $set): array
    {
        // If options is a Closure, evaluate it with $get and $set
        if ($this->options instanceof Closure) {
            return ($this->options)($get, $set);
        }

        // Otherwise, return static options
        return $this->options;
    }

    /**
     * Extract field dependencies from closure by analyzing $get() calls.
     *
     * Parses the closure source code to find all $get('field_name') calls.
     */
    protected function extractClosureDependencies(Closure $closure): array
    {
        try {
            $reflection = new \ReflectionFunction($closure);
            $source = file_get_contents($reflection->getFileName());
            $lines = explode("\n", $source);

            // Get the closure lines
            $startLine = $reflection->getStartLine() - 1;
            $endLine = $reflection->getEndLine();
            $closureCode = implode("\n", array_slice($lines, $startLine, $endLine - $startLine));

            // Find all $get('field_name') calls
            preg_match_all('/\$get\([\'"]([a-zA-Z0-9_]+)[\'"]\)/', $closureCode, $matches);

            return array_unique($matches[1] ?? []);
        } catch (\Exception $e) {
            // If extraction fails, return empty array
            return [];
        }
    }

    /**
     * Get the Vue model binding for Blade components.
     */
    public function vueModel(): string
    {
        return $this->name;
    }

    /**
     * Get the view / contents that represent the Blade component.
     */
    public function render(): string
    {
        if ($this->isHidden()) {
            return '';
        }

        return view($this->view, [
            'component' => $this,
        ])->render();
    }

    protected function getVueComponent(): string
    {
        return 'Select';
    }

    /**
     * Transform options array to format expected by Vue component.
     */
    protected function transformOptions(array $options): array
    {
        $transformed = [];

        foreach ($options as $key => $value) {
            // If already in correct format (has 'value' and 'label' keys)
            if (is_array($value) && isset($value['value']) && isset($value['label'])) {
                $transformed[] = $value;
            } else {
                // Transform key-value pairs to object format
                $transformed[] = [
                    'value' => (string) $key,
                    'label' => $value,
                ];
            }
        }

        return $transformed;
    }

    protected function getVueProps(): array
    {
        // Serialize form schemas to their Inertia props
        $serializedCreateForm = array_map(function ($component) {
            if (method_exists($component, 'toInertiaProps')) {
                return $component->toInertiaProps();
            }

            return $component;
        }, $this->createOptionForm);

        $serializedEditForm = array_map(function ($component) {
            if (method_exists($component, 'toInertiaProps')) {
                return $component->toInertiaProps();
            }

            return $component;
        }, $this->editOptionForm);

        // Detect closure-based options and extract dependencies
        $hasDynamicOptions = $this->options instanceof Closure;
        $closureDependencies = [];

        if ($hasDynamicOptions && empty($this->dependsOn)) {
            // Auto-detect dependencies from closure parameters
            $closureDependencies = $this->extractClosureDependencies($this->options);
        }

        $props = [
            'placeholder' => $this->placeholder,
            'searchable' => $this->searchable,
            'multiple' => $this->multiple,
            'disabled' => $this->disabled,
            'native' => $this->native,
            'relationship' => $this->relationship,
            'titleAttribute' => $this->titleAttribute,
            'preload' => $this->preload,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState(),
            'loadingMessage' => $this->loadingMessage,
            'noSearchResultsMessage' => $this->noSearchResultsMessage,
            'searchPrompt' => $this->searchPrompt,
            'searchingMessage' => $this->searchingMessage,
            'searchDebounce' => $this->searchDebounce,
            'searchableColumns' => $this->searchableColumns,
            'allowHtml' => $this->allowHtml,
            'wrapOptionLabels' => $this->wrapOptionLabels,
            'selectablePlaceholder' => $this->selectablePlaceholder,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'prefixIcon' => $this->prefixIcon,
            'suffixIcon' => $this->suffixIcon,
            'prefixIconColor' => $this->prefixIconColor,
            'suffixIconColor' => $this->suffixIconColor,
            'optionsLimit' => $this->optionsLimit,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'hasCreateOptionForm' => ! empty($this->createOptionForm),
            'hasEditOptionForm' => ! empty($this->editOptionForm),
            'createOptionForm' => $serializedCreateForm,
            'editOptionForm' => $serializedEditForm,
            'dependsOn' => ! empty($this->dependsOn) ? $this->dependsOn : $closureDependencies,
            'optionsUrl' => $this->optionsUrl,
            'hasDynamicOptions' => $hasDynamicOptions,
        ];

        // Only include options if not using relationship or not searchable
        // Searchable relationship selects load options via API
        if (! $this->relationship || ($this->relationship && ! $this->searchable)) {
            $props['options'] = $this->transformOptions($this->getResolvedOptions());
        } else {
            // For searchable relationship selects, pass search URL and params
            $modelClass = $this->getModelClass();
            if ($modelClass) {
                // Build search URL with model and relationship params
                $searchParams = http_build_query([
                    'model' => $modelClass,
                    'relationship' => $this->relationship,
                    'titleAttribute' => $this->titleAttribute ?? 'name',
                ]);
                $props['relationshipSearchUrl'] = '_select/search?'.$searchParams;
                $props['relationshipOptionsUrl'] = '_select/options?'.$searchParams;
                $props['relationshipModel'] = $modelClass;
            }

            // Load initial options (limited) for display
            $initialOptions = $this->loadRelationshipOptionsLimited(50);
            $props['options'] = $this->transformOptions($initialOptions);
        }

        // Check if options are grouped
        if (! empty($props['options']) && is_array(reset($props['options']))) {
            $props['optionsAreGrouped'] = true;
        }

        return $props;
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltSelect';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'options' => $this->getResolvedOptions(),
            'placeholder' => $this->placeholder,
            'searchable' => $this->searchable,
            'multiple' => $this->multiple,
            'enabled' => ! $this->disabled,
            'relationship' => $this->relationship,
            'titleAttribute' => $this->titleAttribute,
            'preload' => $this->preload,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState(),
        ];
    }

    public function toLaraviltProps(): array
    {
        //  Detect closure-based options and extract dependencies
        $hasDynamicOptions = $this->options instanceof Closure;
        $closureDependencies = [];

        if ($hasDynamicOptions && empty($this->dependsOn)) {
            // Auto-detect dependencies from closure parameters
            $closureDependencies = $this->extractClosureDependencies($this->options);
        }

        $props = array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'searchable' => $this->searchable,
            'multiple' => $this->multiple,
            'native' => $this->native,
            'loadingMessage' => $this->loadingMessage,
            'noSearchResultsMessage' => $this->noSearchResultsMessage,
            'searchPrompt' => $this->searchPrompt,
            'searchingMessage' => $this->searchingMessage,
            'searchDebounce' => $this->searchDebounce,
            'optionsLimit' => $this->optionsLimit,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'dependsOn' => ! empty($this->dependsOn) ? $this->dependsOn : $closureDependencies,
            'optionsUrl' => $this->optionsUrl,
            'allowHtml' => $this->allowHtml,
            'wrapOptionLabels' => $this->wrapOptionLabels,
            'isLive' => $this->isLive,
            'isLazy' => $this->isLazy,
            'liveDebounce' => $this->liveDebounce,
            'hasDynamicOptions' => $hasDynamicOptions,
        ]);

        // Handle relationship selects with searchable
        if ($this->relationship && $this->searchable) {
            $modelClass = $this->getModelClass();
            if ($modelClass) {
                // Build search URL with model and relationship params
                $searchParams = http_build_query([
                    'model' => $modelClass,
                    'relationship' => $this->relationship,
                    'titleAttribute' => $this->titleAttribute ?? 'name',
                ]);
                $props['relationshipSearchUrl'] = '_select/search?'.$searchParams;
                $props['relationshipOptionsUrl'] = '_select/options?'.$searchParams;
                $props['relationshipModel'] = $modelClass;
            }

            // Load initial limited options
            $props['options'] = $this->transformOptions($this->loadRelationshipOptionsLimited(50));
        } else {
            // Non-relationship or non-searchable - load all options
            $props['options'] = $this->transformOptions($this->getResolvedOptions());
            // Check if there are more options (set by getResolvedOptions when closure returns many items)
            $props['hasMoreOptions'] = $this->hasMoreOptions;

            // For closure-based options, always provide the search URL
            // This allows fetching selected option labels and pagination
            if ($this->options instanceof Closure) {
                $props['closureOptionsUrl'] = '_select/search';
                $props['fieldName'] = $this->name;

                // Pass context for full closure evaluation (search/pagination)
                if ($this->resourceSlug) {
                    $props['resourceSlug'] = $this->resourceSlug;
                }
                if ($this->relationManagerClass) {
                    $props['relationManager'] = $this->relationManagerClass;
                }
            }
        }

        return $props;
    }
}
