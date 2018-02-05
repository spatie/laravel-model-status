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

    public function getCurrentStatus(): ?Status
    {
        return $this->statuses->last();
    }

    /**
     * @param $name
     * @param $description
     * @return \Spatie\LaravelModelStatus\Models\Status
     * @throws \Spatie\LaravelModelStatus\Exceptions\InvalidStatus
     */
    public function setStatus(string $name, string $description = ''): Status
    {
        if ($this->isValidStatus($name, $description)) {
            $attributes = compact(['name', 'description']);

            return $this->statuses()->create($attributes);
        }

        throw InvalidStatus::create($name, $description);
    }

    public function isValidStatus(string $name, string $description): bool
    {
        return true;
    }

    public function latestStatus(string $name = ''): Status
    {
        if (!empty($name)) {
            return $this->statuses()->where('name', $name)->latest()->first();
        }

        return $this->statuses()->latest()->first();
    }
}
