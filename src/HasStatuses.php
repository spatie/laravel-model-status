<?php

namespace Spatie\LaravelModelStatus;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelModelStatus\Exceptions\InvalidStatus;
use Spatie\LaravelModelStatus\Models\Status;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany(Status::class, 'model');
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }

    public function setStatus(string $name, string $description = ''): self
    {
        if (! $this->isValidStatus($name, $description)) {
            throw InvalidStatus::create($name, $description);
        }

        $attributes = compact('name', 'description');

        $this->statuses()->create($attributes);

        return $this;
    }

    public function isValidStatus(string $name, string $description): bool
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
