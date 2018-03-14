<?php

namespace Spatie\ModelStatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Exceptions\InvalidStatus;

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
        if (!$this->isValidStatus($name, $reason))
        {
            throw InvalidStatus::create($name);
        }

        return $this->forceSetStatus($name, $reason);
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

        if (count($names) < 1)
        {
            return $this->statuses()->orderByDesc('id')->first();
        }

        return $this->statuses()->whereIn('name', $names)->orderByDesc('id')->first();
    }

    public function scopeCurrentStatus(Builder $builder, string $name)
    {
        $builder
            ->whereHas('statuses',
                function (Builder $query) use ($name) {
                    $query
                        ->where('name', $name)
                        ->whereIn('id',
                            function (QueryBuilder $query) use ($name) {
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
            ->whereHas('statuses',
                function (Builder $query) use ($names) {
                    $query
                        ->whereNotIn('name', $names)
                        ->whereIn('id',
                            function (QueryBuilder $query) use ($names) {
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
        return (string)$this->latestStatus();
    }

    public function forceSetStatus(string $name, string $reason = ''): self
    {
        $oldStatus = $this->status;

        $this->statuses()->create([
            'name' => $name,
            'reason' => $reason,
        ]);

        if ($oldStatus !== $name)
        {
            event(new StatusUpdated($oldStatus, $name, $this));
        }

        return $this;
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
