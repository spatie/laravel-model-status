<?php

namespace Spatie\ModelStatus;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Exceptions\InvalidStatus;

trait HasStatuses
{

    /**
     * Relationship between our model and the status model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function statuses(): MorphMany
    {
        return $this->morphMany($this->getStatusModelClassName(), 'model')->latest();
    }

    /**
     * Get the latest model status
     *
     * @return null|\Spatie\ModelStatus\Status
     */
    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    /**
     * Allows checking if a status is valid before actually setting it. This function is used by the setStatus
     * function in order to decide if the new status can be persisted.
     *
     * The forceSetStatus function ignores this function.
     *
     * @param string $name
     * @param string $reason
     * @return bool
     */
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

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $name
     */
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

    /**
     * Easy property access to the latest status
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return (string)$this->latestStatus();
    }


    /**
     * Change the model status
     *
     * @param string $name   The new status name
     * @param string $reason An optional reason to explain the new status
     * @return self
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function setStatus(string $name, string $reason = ''): self
    {
        if (!$this->isValidStatus($name, $reason))
        {
            throw InvalidStatus::create($name);
        }

        return $this->forceSetStatus($name, $reason);
    }

    /**
     * Force a status change without checking the validity of the new status.
     *
     * @param string $name
     * @param string $reason
     * @return self
     */
    public function forceSetStatus(string $name, string $reason = ''): self
    {
        // Keep track of the previous status before updating
        $oldStatus = $this->status;

        // Update current status
        $this->statuses()->create([
            'name'   => $name,
            'reason' => $reason,
        ]);

        // Dispatch an event in case the status has changed
        if ($oldStatus !== $name)
        {
            \event(new StatusUpdated($oldStatus, $name, $this));
        }

        return $this;
    }

    /**
     * Can be overridden if needed to use another status table
     *
     * @return string
     */
    protected function getStatusTableName(): string
    {
        $modelClass = $this->getStatusModelClassName();

        return (new $modelClass)->getTable();
    }

    /**
     * Can be overridden if needed to use another status model
     *
     * @return string
     */
    protected function getStatusModelClassName(): string
    {
        return config('model-status.status_model');
    }
}
