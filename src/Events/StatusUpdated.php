<?php

namespace Spatie\ModelStatus\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Event fired by the HasStatus trait when a status is updated
 *
 * @package Spatie\ModelStatus\Events
 */
class StatusUpdated
{
    protected $oldStatus;
    protected $newStatus;
    protected $model;

    public function __construct(string $oldStatus, string $newStatus, Model $model)
    {
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->model = $model;
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
