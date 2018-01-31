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

    public function getCurrentStatus(): Status
    {
        return $this->statuses->last();
    }

    /**
     * @param $name
     * @param $description
     * @return \Spatie\LaravelModelStatus\Models\Status
     * @throws InvalidStatus
     */
    public function setStatus($name, $description): Status
    {
        if ($this->isValidStatus($name, $description)) {
            $attributes = compact(['name', 'description']);

            $statusSet = $this->statuses()->create($attributes);

            return $statusSet;
        }

        throw new InvalidStatus();
    }

    public function isValidStatus($name, $description): bool
    {
        return true;
    }

    public function findLastStatus($name): Status
    {
        return $this->statuses()->where('name', $name)->latest()->first();
    }
}
