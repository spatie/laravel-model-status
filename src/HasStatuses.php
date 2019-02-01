<?php

namespace Spatie\ModelStatus;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany($this->getStatusModelClassName(), 'model', 'model_type', $this->getModelKeyColumnName())
                    ->latest('id');
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    public function setStatus(string $name, ?string $reason = null): self
    {
        if (! $this->isValidStatus($name, $reason)) {
            throw InvalidStatus::create($name);
        }

        return $this->forceSetStatus($name, $reason);
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
        $names = is_array($names) ? Arr::flatten($names) : func_get_args();

        $statuses = $this->relationLoaded('statuses') ? $this->statuses : $this->statuses();

        if (count($names) < 1) {
            return $statuses->first();
        }

        return $statuses->whereIn('name', $names)->first();
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
                        ->whereIn(
                            'id',
                            function (QueryBuilder $query) {
                                $query
                                    ->select(DB::raw('max(id)'))
                                    ->from($this->getStatusTableName())
                                    ->where('model_type', $this->getStatusModelType())
                                    ->groupBy($this->getModelKeyColumnName());
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
                        ->whereIn(
                            'id',
                            function (QueryBuilder $query) use ($names) {
                                $query
                                    ->select(DB::raw('max(id)'))
                                    ->from($this->getStatusTableName())
                                    ->where('model_type', $this->getStatusModelType())
                                    ->groupBy($this->getModelKeyColumnName());
                            }
                        );
                }
            )
            ->orWhereDoesntHave('statuses');
    }

    public function getStatusAttribute(): string
    {
        return (string) $this->latestStatus();
    }

    public function forceSetStatus(string $name, ?string $reason = null): self
    {
        $oldStatus = $this->latestStatus();

        $newStatus = $this->statuses()->create([
            'name'   => $name,
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

    protected function getStatusModelType(): string
    {
        return array_search(static::class, Relation::morphMap()) ?: static::class;
    }
}
