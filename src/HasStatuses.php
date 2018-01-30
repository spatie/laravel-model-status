<?php


namespace Spatie\LaravelStatus;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelStatus\Exception\StatusError;
use Spatie\LaravelStatus\Models\Status;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany(Status::class, 'status');
    }

    public function getStatus(): Status
    {
        return $this->statuses->last();
    }

    /**
     * @param $status_name
     * @param $status_explanation
     * @return Status
     * @throws \Spatie\LaravelStatus\Exception\StatusError
     */
    public function setStatus($status_name, $status_explanation): Status
    {
        if ($this->isValidStatus($status_name, $status_explanation)) {
            $StatusSet = $this->statuses()->create(['name'=>$status_name,'explanation'=>$status_explanation]);
            return $StatusSet;
        }
        throw new StatusError("The status is not valid, check the status or adjust the isValidStatus method. ");
    }

    public function isValidStatus($status_name, $status_explanation): bool
    {
        return true;
    }
}
