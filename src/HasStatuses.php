<?php

namespace Spatie\ModelStatus;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany($this->getStatusModelClassName(), 'model')->latest();
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    public function setStatus(string $name, string $reason = ''): self
    {
        if (! $this->isValidStatus($name, $reason)) {
            throw InvalidStatus::create($name, $reason);
        }

        $this->statuses()->create([
            'name' => $name,
            'reason' => $reason,
        ]);

        return $this;
    }

    public function isValidStatus(string $name, string $reason = ''): bool
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
        $names = is_array($names) ? array_flatten($names) : func_get_args();

        if (count($names) < 1) {
            return $this->statuses()->orderByDesc('id')->first();
        }

        return $this->statuses()->whereIn('name', $names)->orderByDesc('id')->first();
    }

    public function scopeCurrentStatus(Builder $builder, string $name)
    {
        $builder
            ->whereHas('statuses', function (Builder $query) use ($name) {
                $query
                    ->where('name', $name)
                    ->whereIn('id', function (QueryBuilder $query) use ($name) {
                        $query
                            ->select(DB::raw('max(id)'))
                            ->from($this->getStatusTableName())
                            ->where('model_type', static::class)
                            ->groupBy('model_id');
                    });
            });
    }

    /**
     * @param string|array $names
     *
     * @return void
     **/
    public function scopeOtherCurrentStatus(Builder $builder, ...$names)
    {
        $names = is_array($names) ? array_flatten($names) : func_get_args();
        $builder
            ->whereHas('statuses', function (Builder $query) use ($names) {
                $query
                    ->whereNotIn('name', $names)
                    ->whereIn('id', function (QueryBuilder $query) use ($names) {
                        $query
                            ->select(DB::raw('max(id)'))
                            ->from($this->getStatusTableName())
                            ->where('model_type', static::class)
                            ->groupBy('model_id');
                    });
            })
            ->orWhereDoesntHave('statuses');
    }

    public function getStatusAttribute(): string
    {
        return (string) $this->latestStatus();
    }

    protected function getStatusTableName(): string
    {
        $modelClass = $this->getStatusModelClassName();

        return (new $modelClass)->getTable();
    }

    protected function getStatusModelClassName(): string
    {
        return config('model-status.status_model');
    }
}
