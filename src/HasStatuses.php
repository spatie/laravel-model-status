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

    public function setStatus(string $name, string $description = ''): self
    {
        if (! $this->isValidStatus($name, $description)) {
            throw InvalidStatus::create($name, $description);
        }

        $attributes = compact(['name', 'description']);

        $this->statuses()->create($attributes);

        return $this;
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
