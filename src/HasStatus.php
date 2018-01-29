<?php


namespace Spatie\LaravelEloquentStatus;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelEloquentStatus\Models\Status;

trait HasStatus
{
    public function getStatus(): Status
    {
        return $this->statuses->last();
    }


    public function setStatus($status_name, $status_explenation): Status
    {
        $setStatus = $this->statuses()->create(['name'=>$status_name,'explanation'=>$status_explenation]);
        return $setStatus;
    }

    public function statuses(): MorphMany
    {
        return $this->morphMany(Status::class, 'status');
    }

    /**
     * checking to see if the data is valid (to override)
     *
     * @return boolean Returns true if the passed data is valid
     */
    public function isStatusValid()
    {
        return true;
    }
}
