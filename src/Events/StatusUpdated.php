<?php

namespace Spatie\ModelStatus\Events;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\Status;

class StatusUpdated
{
    /** @var \Spatie\ModelStatus\Status|null */
    public $oldStatus;

    /** @var \Spatie\ModelStatus\Status */
    public $newStatus;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $model;

    public function __construct(?Status $oldStatus, Status $newStatus, Model $model)
    {
        $this->oldStatus = $oldStatus;

        $this->newStatus = $newStatus;

        $this->model = $model;
    }
}
