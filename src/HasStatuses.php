<?php

namespace Spatie\ModelStatus;

use BackedEnum;
use InvalidArgumentException;
use ReflectionClass;
use UnitEnum;
use \Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Spatie\ModelStatus\Attributes\UseStatus;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Exceptions\InvalidStatus;

trait HasStatuses
{
    /**
     * @var array<string, array{
     *     enum: class-string<UnitEnum>,
     *     strict: bool,
     *     allowedNames: array<int, string>,
     *     caseMap: array<string, UnitEnum>
     * }|null>
     */
    private static array $statusEnumConfiguration = [];

    public function statuses(): MorphMany
    {
        return $this->morphMany($this->getStatusModelClassName(), 'model', 'model_type', $this->getModelKeyColumnName())
            ->latest('id');
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    public function setStatus(string|UnitEnum $name, ?string $reason = null): self
    {
        $normalizedName = $this->normalizeInput($name);
        $this->isValidByAttribute($normalizedName, $name);

        if (! $this->isValidStatus($normalizedName, $reason)) {
            throw InvalidStatus::create($normalizedName);
        }

        return $this->forceSetStatus($normalizedName, $reason);
    }

    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        return true;
    }

    /**
     * @param string|array $names
     *
     * @return null|Status
     */
    public function latestStatus(...$names): ?Status
    {
        $statuses = $this->relationLoaded('statuses') ? $this->statuses : $this->statuses();

        $names = is_array($names) ? Arr::flatten($names) : func_get_args();
        if (count($names) < 1) {
            return $statuses->first();
        }

        return $statuses->whereIn('name', $names)->first();
    }

    /**
     * Check if the model has ever had a status with the given name.
     *
     * This method determines whether the current model instance has ever had a status
     * with the specified name.
     *
     * @param string $name The name of the status to check for.
     *
     * @return bool Returns true if the model has ever had the status with the given name,
     *              otherwise returns false.
     */
    public function hasEverHadStatus($name): bool
    {
        $statuses = $this->relationLoaded('statuses') ? $this->statuses : $this->statuses();

        return $statuses->where('name', $name)->count() > 0;
    }

    /**
     * Check if the model has never had a status with the given name.
     *
     * This method determines whether the current model instance has never had a status
     * with the specified name by negating the result of hasEverHadStatus.
     *
     * @param string $name The name of the status to check for.
     *
     * @return bool Returns true if the model has never had the status with the given name,
     *              otherwise returns false.
     */
    public function hasNeverHadStatus($name): bool
    {
        return ! $this->hasEverHadStatus($name);
    }

    public function deleteStatus(...$names)
    {
        $names = is_array($names) ? Arr::flatten($names) : func_get_args();
        if (count($names) < 1) {
            return $this;
        }

        $this->statuses()->whereIn('name', $names)->delete();
    }

    public function scopeCurrentStatus(Builder $builder, ...$names)
    {
        $names = is_array($names) ? Arr::flatten($names) : func_get_args();
        $builder
            ->whereHas(
                'statuses',
                function (Builder $query) use ($names) {
                    $query
                        ->whereIn('name', $names)
                        ->where(
                            'id',
                            function (QueryBuilder $subQuery) {
                                $subQuery
                                    ->selectRaw('max(id)')
                                    ->from($this->getStatusTableName())
                                    ->where('model_type', $this->getStatusModelType())
                                    ->whereColumn($this->getModelKeyColumnName(), $this->getQualifiedKeyName());
                            }
                        );
                }
            );
    }

    /**
     * @param string|array $names
     *
     * @return void
     **/
    public function scopeOtherCurrentStatus(Builder $builder, ...$names)
    {
        $names = is_array($names) ? Arr::flatten($names) : func_get_args();
        $builder
            ->whereHas(
                'statuses',
                function (Builder $query) use ($names) {
                    $query
                        ->whereNotIn('name', $names)
                        ->where(
                            'id',
                            function (QueryBuilder $subQuery) {
                                $subQuery
                                    ->selectRaw('max(id)')
                                    ->from($this->getStatusTableName())
                                    ->where('model_type', $this->getStatusModelType())
                                    ->whereColumn($this->getModelKeyColumnName(), $this->getQualifiedKeyName());
                            }
                        );
                }
            )
            ->orWhereDoesntHave('statuses');
    }

    public function forceSetStatus(string|UnitEnum $name, ?string $reason = null): self
    {
        $normalizedName = $this->normalizeInput($name);
        $this->isValidByAttribute($normalizedName, $name, true);

        $oldStatus = $this->latestStatus();

        $newStatus = $this->statuses()->create([
            'name' => $normalizedName,
            'reason' => $reason,
        ]);

        event(new StatusUpdated($oldStatus, $newStatus, $this));

        return $this;
    }

    public function statusEnum(): ?UnitEnum
    {
        $status = $this->latestStatus();

        if ($status === null) {
            return null;
        }

        $configuration = $this->getStatusEnumConfiguration();

        if ($configuration === null) {
            return null;
        }

        return $configuration['caseMap'][$status->name] ?? null;
    }

    protected function getStatusTableName(): string
    {
        $modelClass = $this->getStatusModelClassName();

        return (new $modelClass)->getTable();
    }

    protected function getModelKeyColumnName(): string
    {
        return config('model-status.model_primary_key_attribute') ?? 'model_id';
    }

    protected function getStatusModelClassName(): string
    {
        return config('model-status.status_model');
    }

    protected function getStatusAttributeName(): string
    {
        return config('model-status.status_attribute') ?? 'status';
    }

    protected function getStatusModelType(): string
    {
        return array_search(static::class, Relation::morphMap()) ?: static::class;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key): mixed
    {
        if ($key === $this->getStatusAttributeName()) {
            return (string) $this->latestStatus();
        }

        return parent::__get($key);
    }
    /*
    * Get all available status names for the model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getStatusNames(): Collection
    {
        $statusModel = app($this->getStatusModelClassName());

        return $statusModel->pluck('name');
    }

    public function hasStatus(string $name): bool
    {
        return $this->statuses()->where('name', $name)->exists();
    }

    protected function normalizeInput(string|UnitEnum $input): string
    {
        if ($input instanceof BackedEnum) {
            return (string) $input->value;
        }

        if ($input instanceof UnitEnum) {
            return $input->name;
        }

        return $input;
    }

    protected function isValidByAttribute(
        string $normalizedName,
        string|UnitEnum $input,
        bool $strict = false
    ): void {
        $configuration = $this->getStatusEnumConfiguration();

        if ($configuration === null) {
            return;
        }

        if ($strict && ! $configuration['strict']) {
            return;
        }

        if ($input instanceof UnitEnum && ! $input instanceof $configuration['enum']) {
            throw InvalidStatus::create($normalizedName);
        }

        if (! in_array($normalizedName, $configuration['allowedNames'], true)) {
            throw InvalidStatus::create($normalizedName);
        }
    }

    /**
     * @return array{
     *     enum: class-string<UnitEnum>,
     *     strict: bool,
     *     allowedNames: array<int, string>,
     *     caseMap: array<string, UnitEnum>
     * }|null
     */
    protected function getStatusEnumConfiguration(): ?array
    {
        if (array_key_exists(static::class, self::$statusEnumConfiguration)) {
            return self::$statusEnumConfiguration[static::class];
        }

        $attributes = new ReflectionClass(static::class)->getAttributes(UseStatus::class);

        if (count($attributes) === 0) {
            self::$statusEnumConfiguration[static::class] = null;

            return null;
        }

        if (count($attributes) > 1) {
            throw new InvalidArgumentException(
                "The model `".static::class.'` can only define one #[UseStatus] attribute.'
            );
        }

        /** @var UseStatus $useStatus */
        $useStatus = $attributes[0]->newInstance();
        $enum = $useStatus->enum;

        if (! enum_exists($enum)) {
            throw new InvalidArgumentException(
                "The class `{$enum}` configured on model `".static::class.'` is not an enum.'
            );
        }

        $allowedNames = [];
        $caseMap = [];

        foreach ($enum::cases() as $case) {
            $normalizedName = $this->normalizeInput($case);
            $allowedNames[] = $normalizedName;
            $caseMap[$normalizedName] = $case;
        }

        $configuration = [
            'enum' => $enum,
            'strict' => $useStatus->strict,
            'allowedNames' => $allowedNames,
            'caseMap' => $caseMap,
        ];

        self::$statusEnumConfiguration[static::class] = $configuration;

        return $configuration;
    }
}
