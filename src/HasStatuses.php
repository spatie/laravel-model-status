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

    public function currentStatus(): ?Status
    {
        return $this->latestStatus();
    }

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

    /**
     * @param string|array $name
     *
     * @return \Spatie\LaravelModelStatus\Models\Status|null
     */
    public function latestStatus($name = []): ?Status
    {
        $name = is_array($name) ? $name : func_get_args();

        if (count($name) > 0) {
            $result = $this->statuses()->whereIn('name', $name)->latest()->orderByDesc('id')->first();

            if ($result) {
                return $result;
            }
        }

        return $this->statuses()->latest()->orderByDesc('id')->first();
    }
}
