<?php

namespace Spatie\ModelStatus;

use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany(ModelStatusServiceProvider::getStatusModel(), 'model');
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
            return $this->statuses()->latest()->orderByDesc('id')->first();
        }

        return $this->statuses()->whereIn('name', $names)->latest()->orderByDesc('id')->first();
    }

    public function clearStatuses(): ?bool
    {
        return $this->statuses()->delete();
    }
}
