<?php

namespace Spatie\ModelStatus;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\ModelStatus\Exceptions\InvalidStatus;

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

    public function latestStatus(string ...$name): ?Status
    {
        if (empty($name)) {
            return $this->statuses()->latest()->orderByDesc('id')->first();
        }

        return $this->statuses()->whereIn('name', $name)->latest()->orderByDesc('id')->first();
    }
}
