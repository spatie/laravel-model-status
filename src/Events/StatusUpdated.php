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
    /** @var string */
    private $oldStatus;

    /** @var string */
    private $newStatus;

    /** @var \Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * StatusUpdated constructor.
     *
     * @param string                              $oldStatus
     * @param string                              $newStatus
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(string $oldStatus, string $newStatus, Model $model)
    {
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    /**
     * @return string
     */
    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}
