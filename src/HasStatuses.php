<?php

namespace Spatie\ModelStatus;

use Illuminate\Database\Eloquent\Builder;
use DB;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany(ModelStatusServiceProvider::getStatusModel(), 'model')->latest();
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    public function setStatus(string $name, string $reason = ''): self
    {
        if (!$this->isValidStatus($name, $reason)) {
            throw InvalidStatus::create($name, $reason);
        }

        $attributes = compact('name', 'reason');

        $this->statuses()->create($attributes);

        return $this;
    }

    public function isValidStatus(string $name, string $reason = ''): bool
    {
        return true;
    }

    /**
     * @param string|array $names
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

    public function scopeHasStatus(Builder $builder, string $name)
    {
        return $builder
            ->whereHas('statuses', function (Builder $query) use ($name) {
                $query
                    ->where('name', $name)
                    ->whereIn('id', function ($query) use ($name) {
                        $query
                            ->select(DB::raw('max(id)'))
                            ->from('statuses')
                            ->groupBy('model_id');
                    });
            });
    }

    public function getStatusAttribute(): string
    {
        return $this->latestStatus();
    }
}
