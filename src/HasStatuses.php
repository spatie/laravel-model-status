<?php


namespace Spatie\LaravelStatus;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use phpDocumentor\Reflection\Types\Mixed_;
use PHPUnit\Exception;
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


    public function setStatus($status_name, $status_explanation): ?Status
    {
        if ($this->isValidStatus($status_name, $status_explanation)) {
            $StatusSet = $this->statuses()->create(['name'=>$status_name,'explanation'=>$status_explanation]);
            return $StatusSet;
        }
        return null;
    }

    public function isValidStatus($status_name, $status_explanation): bool
    {
        return true;
    }
}
