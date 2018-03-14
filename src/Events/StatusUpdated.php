<?php

namespace Spatie\ModelStatus\Events;

use Illuminate\Database\Eloquent\Model;

class StatusUpdated
{
    /** @var string */
    public $oldStatus;

    /** @var string */
    public $newStatus;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $model;

    public function __construct(string $oldStatus, string $newStatus, Model $model)
    {
        $this->oldStatus = $oldStatus;

        $this->newStatus = $newStatus;

        $this->model = $model;
    }
}
