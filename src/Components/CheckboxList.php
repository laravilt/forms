<?php

namespace Laravilt\Forms\Components;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravilt\Panel\Facades\Panel;

class CheckboxList extends Field
{
    protected string $view = 'laravilt-forms::components.fields.checkbox-list';

    protected array|Closure $options = [];

    protected bool $inline = false;

    protected ?int $columns = null;

    protected ?string $relationshipName = null;

    protected ?string $relationshipTitleAttribute = null;

    protected ?Closure $relationshipModifyQueryUsing = null;

    protected bool $searchable = false;

    protected bool $bulkToggleable = false;

    protected string $gridDirection = 'column';

    /**
     * The form model class (for relationship resolution).
     */
    protected ?string $formModelClass = null;

    /**
     * The attribute to group options by (e.g., 'guard_name').
     */
    protected ?string $groupByAttribute = null;

    /**
     * Custom group labels keyed by group value.
     */
    protected array $groupLabels = [];

    /**
     * Whether to show select all toggle for each group.
     */
    protected bool $groupSelectAll = false;

    /**
     * Default group value for filtering (e.g., 'web').
     */
    protected ?string $defaultGroup = null;

    /**
     * Whether to group by resource name extracted from permission name.
     */
    protected bool $groupByResource = false;

    /**
     * Whether group sections are collapsible.
     */
    protected bool $collapsible = true;

    /**
     * Resource classes to look up labels from.
     *
     * @var array<string, class-string>
     */
    protected array $resourceClasses = [];

    /**
     * Common permission action prefixes.
     */
    protected array $actionPrefixes = [
        'view_any',
        'view',
        'create',
        'update',
        'delete_any',
        'delete',
        'restore_any',
        'restore',
        'force_delete_any',
        'force_delete',
        'replicate',
        'reorder',
        'export',
        'import',
        'impersonate',
        'manage',
        'access',
        'publish',
        'unpublish',
        'archive',
        'unarchive',
        'approve',
        'reject',
        'assign',
        'unassign',
        'attach',
        'detach',
        'sync',
        'toggle',
        'bulk_delete',
        'bulk_update',
    ];

    /**
     * Set the form model class (used by Schema for relationship resolution).
     *
     * @param  class-string  $model
     */
    public function formModel(string $model): static
    {
        $this->formModelClass = $model;

        return $this;
    }

    /**
     * Group options by an attribute (e.g., 'guard_name').
     * This enables grouped display with optional select all per group.
     */
    public function groupBy(string $attribute, array $labels = []): static
    {
        $this->groupByAttribute = $attribute;
        $this->groupLabels = $labels;
        $this->groupSelectAll = true;

        return $this;
    }

    /**
     * Set custom labels for groups.
     */
    public function groupLabels(array $labels): static
    {
        $this->groupLabels = $labels;

        return $this;
    }

    /**
     * Enable/disable select all toggle for groups.
     */
    public function groupSelectAll(bool $condition = true): static
    {
        $this->groupSelectAll = $condition;

        return $this;
    }

    /**
     * Enable/disable collapsible sections.
     */
    public function collapsible(bool $condition = true): static
    {
        $this->collapsible = $condition;

        return $this;
    }

    /**
     * Set default group to filter by (e.g., 'web' for guard_name).
     */
    public function defaultGroup(?string $group): static
    {
        $this->defaultGroup = $group;

        return $this;
    }

    /**
     * Enable grouping by resource name extracted from permission names.
     * This parses permission names like 'view_user' to group under 'User' resource.
     *
     * @param  array<string, class-string>  $resourceClasses  Optional mapping of resource slugs to resource classes
     */
    public function groupByResource(array $resourceClasses = []): static
    {
        $this->groupByResource = true;
        $this->resourceClasses = $resourceClasses;
        $this->groupSelectAll = true;

        // If no resource classes provided, try to get from current panel
        if (empty($this->resourceClasses)) {
            $this->resourceClasses = $this->discoverResourceClasses();
        }

        return $this;
    }

    /**
     * Discover resource classes from the current panel.
     *
     * @return array<string, class-string>
     */
    protected function discoverResourceClasses(): array
    {
        $resources = [];

        try {
            $panel = Panel::getCurrent();
            if ($panel) {
                foreach ($panel->getResources() as $resourceClass) {
                    // Get the resource slug (e.g., 'users' -> 'user')
                    $slug = $resourceClass::getSlug();
                    $singular = str($slug)->singular()->toString();
                    $resources[$singular] = $resourceClass;

                    // Also add plural form
                    $resources[$slug] = $resourceClass;
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if panel not available
        }

        return $resources;
    }

    /**
     * Parse a permission name to extract action and resource.
     *
     * @return array{action: string, resource: string}|null
     */
    protected function parsePermissionName(string $permissionName): ?array
    {
        // Sort action prefixes by length (longest first) to match correctly
        $sortedPrefixes = $this->actionPrefixes;
        usort($sortedPrefixes, fn ($a, $b) => strlen($b) - strlen($a));

        foreach ($sortedPrefixes as $action) {
            $prefix = $action.'_';
            if (str_starts_with($permissionName, $prefix)) {
                $resource = substr($permissionName, strlen($prefix));

                return [
                    'action' => $action,
                    'resource' => $resource,
                ];
            }
        }

        // If no known action prefix, try to split by underscore
        $parts = explode('_', $permissionName, 2);
        if (count($parts) === 2) {
            return [
                'action' => $parts[0],
                'resource' => $parts[1],
            ];
        }

        return null;
    }

    /**
     * Get the label for a resource.
     */
    protected function getResourceLabel(string $resourceSlug): string
    {
        // Check if we have a resource class for this slug
        if (isset($this->resourceClasses[$resourceSlug])) {
            $resourceClass = $this->resourceClasses[$resourceSlug];
            if (method_exists($resourceClass, 'getPluralModelLabel')) {
                return $resourceClass::getPluralModelLabel();
            }
            if (method_exists($resourceClass, 'getModelLabel')) {
                return $resourceClass::getModelLabel();
            }
        }

        // Fallback: convert slug to title case
        return str($resourceSlug)->replace('_', ' ')->title()->toString();
    }

    /**
     * Get the label for an action.
     */
    protected function getActionLabel(string $action): string
    {
        // Use translation keys for actions
        $translationKey = 'forms::permissions.actions.'.$action;
        $translated = __($translationKey);

        // If translation exists, use it
        if ($translated !== $translationKey) {
            return $translated;
        }

        // Fallback to formatted action name
        return str($action)->replace('_', ' ')->title()->toString();
    }

    /**
     * Set the grid direction ('row' or 'column').
     * - 'column': items flow vertically then wrap to next column
     * - 'row': items flow horizontally then wrap to next row
     */
    public function gridDirection(string $direction): static
    {
        $this->gridDirection = $direction;

        return $this;
    }

    /**
     * Set options for the checkbox list.
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Configure a BelongsToMany relationship for the checkbox list.
     */
    public function relationship(string $name, string $titleAttribute, ?Closure $modifyQueryUsing = null): static
    {
        $this->relationshipName = $name;
        $this->relationshipTitleAttribute = $titleAttribute;
        $this->relationshipModifyQueryUsing = $modifyQueryUsing;

        return $this;
    }

    /**
     * Make the checkbox list searchable.
     */
    public function searchable(bool $condition = true): static
    {
        $this->searchable = $condition;

        return $this;
    }

    /**
     * Enable bulk toggle (select all / deselect all).
     */
    public function bulkToggleable(bool $condition = true): static
    {
        $this->bulkToggleable = $condition;

        return $this;
    }

    /**
     * Set inline layout.
     */
    public function inline(bool $condition = true): static
    {
        $this->inline = $condition;

        return $this;
    }

    /**
     * Set grid columns.
     */
    public function columns(?int $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get the relationship name.
     */
    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }

    /**
     * Get the relationship title attribute.
     */
    public function getRelationshipTitleAttribute(): ?string
    {
        return $this->relationshipTitleAttribute;
    }

    /**
     * Get the options array (evaluating relationship if configured).
     */
    protected function getOptions(): array
    {
        // If relationship is set, get options from related model
        if ($this->relationshipName !== null) {
            return $this->getRelationshipOptions();
        }

        if ($this->options instanceof Closure) {
            return ($this->options)();
        }

        return $this->options;
    }

    /**
     * Get options from a relationship.
     */
    protected function getRelationshipOptions(): array
    {
        // Get the model from the resource or record
        $record = $this->getEvaluationRecord();

        if ($record && method_exists($record, $this->relationshipName)) {
            // Get the relationship query from the record
            $relationship = $record->{$this->relationshipName}();
            $relatedModel = $relationship->getRelated();
            $query = $relatedModel::query();
        } else {
            // Try to get the related model class from the form model or fallback
            $relatedModelClass = $this->getRelatedModelClassFromFormModel();

            if (! $relatedModelClass) {
                // Fall back to guessing the related model class
                $relatedModelClass = $this->getRelatedModelClass();
            }

            if (! $relatedModelClass) {
                return [];
            }

            $query = $relatedModelClass::query();
        }

        // Filter by default group if set (e.g., guard_name = 'web')
        if ($this->defaultGroup !== null) {
            // For permissions, filter by guard_name
            $query->where('guard_name', $this->defaultGroup);
        }

        // Apply the query modifier if provided
        if ($this->relationshipModifyQueryUsing !== null) {
            $query = ($this->relationshipModifyQueryUsing)($query) ?? $query;
        }

        $results = $query->get();

        $options = [];
        foreach ($results as $model) {
            $permissionName = $model->{$this->relationshipTitleAttribute};

            // If grouping by resource, parse the permission name
            if ($this->groupByResource) {
                $parsed = $this->parsePermissionName($permissionName);
                if ($parsed) {
                    $option = [
                        'value' => $model->getKey(),
                        'label' => $this->getActionLabel($parsed['action']),
                        'group' => $parsed['resource'],
                        'groupLabel' => $this->getResourceLabel($parsed['resource']),
                        'action' => $parsed['action'],
                        'permissionName' => $permissionName,
                    ];
                } else {
                    // Permission doesn't match pattern, put in "Other" group
                    $option = [
                        'value' => $model->getKey(),
                        'label' => $permissionName,
                        'group' => '_other',
                        'groupLabel' => __('forms::permissions.other'),
                        'action' => null,
                        'permissionName' => $permissionName,
                    ];
                }
            } else {
                $option = [
                    'value' => $model->getKey(),
                    'label' => $permissionName,
                ];

                // Include group field if groupBy attribute is set
                if ($this->groupByAttribute !== null) {
                    $option['group'] = $model->{$this->groupByAttribute} ?? null;
                }
            }

            $options[] = $option;
        }

        return $options;
    }

    /**
     * Get the related model class from the form model's relationship definition.
     */
    protected function getRelatedModelClassFromFormModel(): ?string
    {
        if (! $this->formModelClass || ! $this->relationshipName) {
            return null;
        }

        try {
            // Create a temporary model instance to get the relationship
            $modelInstance = new $this->formModelClass;

            if (! method_exists($modelInstance, $this->relationshipName)) {
                return null;
            }

            $relationship = $modelInstance->{$this->relationshipName}();

            if ($relationship instanceof Relation) {
                return get_class($relationship->getRelated());
            }
        } catch (\Throwable $e) {
            // Failed to get related model class
        }

        return null;
    }

    /**
     * Try to determine the related model class.
     */
    protected function getRelatedModelClass(): ?string
    {
        // Common patterns for relationship names to model classes
        $relationshipName = $this->relationshipName;

        // Try to find a model based on common conventions
        // e.g., 'permissions' -> Permission, 'roles' -> Role
        $singular = str($relationshipName)->singular()->studly()->toString();

        $possibleClasses = [
            "App\\Models\\{$singular}",
            "Spatie\\Permission\\Models\\{$singular}",
        ];

        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    protected function getVueComponent(): string
    {
        return 'CheckboxList';
    }

    /**
     * Serialize component for Laravilt (Inertia + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'options' => $this->getOptions(),
            'inline' => $this->inline,
            'columns' => $this->columns,
            'gridDirection' => $this->gridDirection,
            'searchable' => $this->searchable,
            'bulkToggleable' => $this->bulkToggleable,
            'relationship' => $this->relationshipName,
            'groupBy' => $this->groupByResource ? 'resource' : $this->groupByAttribute,
            'groupByResource' => $this->groupByResource,
            'groupLabels' => $this->groupLabels,
            'groupSelectAll' => $this->groupSelectAll,
            'defaultGroup' => $this->defaultGroup,
            'collapsible' => $this->collapsible,
        ]);
    }

    protected function getVueProps(): array
    {
        return [
            'options' => $this->getOptions(),
            'disabled' => $this->disabled,
            'inline' => $this->inline,
            'columns' => $this->columns,
            'gridDirection' => $this->gridDirection,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? [],
            'searchable' => $this->searchable,
            'bulkToggleable' => $this->bulkToggleable,
            'relationship' => $this->relationshipName,
        ];
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltCheckboxList';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'options' => $this->getOptions(),
            'enabled' => ! $this->disabled,
            'inline' => $this->inline,
            'columns' => $this->columns,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? [],
        ];
    }
}
