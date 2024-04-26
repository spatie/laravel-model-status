<?php

namespace Spatie\ModelStatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Exceptions\InvalidEnumClass;
use Spatie\ModelStatus\Exceptions\InvalidEnumType;
use Spatie\ModelStatus\Exceptions\InvalidStatus;

trait HasStatuses
{

    abstract public static function getStatusEnumClass(): string;

    public function statuses(): MorphMany
    {
        if (! self::enumIsStringBacked()) {
            throw InvalidEnumType::create(self::getStatusEnumClass());
        }

        return $this->morphMany($this->getStatusModelClassName(), 'model', 'model_type', $this->getModelKeyColumnName())
            ->latest('id');
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    public function setStatus($statusEnum, ?string $reason = null): self
    {
        if (! $this->isValidStatus($statusEnum, $reason)) {
            throw InvalidStatus::create($statusEnum->value);
        }

        return $this->forceSetStatus($statusEnum, $reason);
    }

    public function isValidStatus($statusEnum, ?string $reason = null): bool
    {
        return true;
    }

    /**
     * @param object|array $names
     *
     * @return null|Status
     */
    public function latestStatus(...$statusEnums): ?Status
    {
        $statuses = $this->relationLoaded('statuses') ? $this->statuses : $this->statuses();

        $statusEnums = is_array($statusEnums) ? Arr::flatten($statusEnums) : func_get_args();

        Arr::map($statusEnums, fn ($statusEnum) => $statusEnum->value);

        if (count($statusEnums) < 1) {
            return $statuses->first();
        }

        return $statuses->whereIn('name', $statusEnums)->first();
    }

    public function hasEverHadStatus($statusEnum): bool
    {
        $statuses = $this->relationLoaded('statuses') ? $this->statuses : $this->statuses();

        return $statuses->where('name', $statusEnum->value)->count() > 0;
    }

    public function deleteStatus(...$statusEnums)
    {
        $statusEnums = is_array($statusEnums) ? Arr::flatten($statusEnums) : func_get_args();

        Arr::map($statusEnums, fn ($statusEnum) => $statusEnum->value);

        if (count($statusEnums) < 1) {
            return $this;
        }

        $this->statuses()->whereIn('name', $statusEnums)->delete();
    }

    public function scopeCurrentStatus(Builder $builder, ...$statusEnums)
    {
        $statusEnums = is_array($statusEnums) ? Arr::flatten($statusEnums) : func_get_args();

        Arr::map($statusEnums, fn ($statusEnum) => $statusEnum->value);

        $builder
            ->whereHas(
                'statuses',
                function (Builder $query) use ($statusEnums) {
                    $query
                        ->whereIn('name', $statusEnums)
                        ->whereIn(
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
     * @param object|array $names
     *
     * @return void
     **/
    public function scopeOtherCurrentStatus(Builder $builder, ...$statusEnums)
    {
        $statusEnums = is_array($statusEnums) ? Arr::flatten($statusEnums) : func_get_args();

        Arr::map($statusEnums, fn ($statusEnum) => $statusEnum->value);

        $builder
            ->whereHas(
                'statuses',
                function (Builder $query) use ($statusEnums) {
                    $query
                        ->whereNotIn('name', $statusEnums)
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


    private static function isInstanceOfEnum($statusEnum): bool
    {
        $statusEnumType = self::getStatusEnumClass();

        return $statusEnum instanceof $statusEnumType;
    }

    private static function enumIsStringBacked(): bool
    {
        return method_exists(self::getStatusEnumClass(), 'from');
    }


    public function forceSetStatus($statusEnum, ?string $reason = null): self
    {
        if (! self::isInstanceOfEnum($statusEnum)) {
            throw InvalidEnumClass::create(self::getStatusEnumClass());
        }

        $oldStatus = $this->latestStatus();

        $newStatus = $this->statuses()->create([
            'name' => $statusEnum->value,
            'reason' => $reason,
        ]);

        event(new StatusUpdated($oldStatus, $newStatus, $this));

        return $this;
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
            return $this->getStatusEnumClass()::from((string) $this->latestStatus());
        }

        return parent::__get($key);
    }

    public function hasStatus($statusEnum): bool
    {
        return $this->statuses()->where('name', $statusEnum->value)->exists();
    }
}
