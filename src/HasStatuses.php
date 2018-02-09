<?php

namespace Spatie\ModelStatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStatuses
{
    public function statuses(): MorphMany
    {
        return $this->morphMany(ModelStatusServiceProvider::getStatusModel(), 'model')->latest();
    }

    public function status(): ?Status
    {
        return $this->latestStatus();
    }


    public function setStatus(string $name, string $reason = ''): self
    {
        if (!$this->isValidStatus($name, $reason)) {
            throw InvalidStatus::create($name, $reason);
        }

        $attributes = compact('name', 'reason');

        $this->statuses()->create($attributes);

        return $this;
    }

    public function isValidStatus(string $name, string $reason = ''): bool
    {
        return true;
    }

    /**
     * @param string|array $names
     * @return null|Status
     */
    public function latestStatus(...$names): ?Status
    {
        $names = is_array($names) ? array_flatten($names) : func_get_args();

        if (count($names) < 1) {
            return $this->statuses()->orderByDesc('id')->first();
        }

        return $this->statuses()->whereIn('name', $names)->orderByDesc('id')->first();
    }

//    public function scopeHasStatus(Builder $query, string $status): Builder
//    {
//        $modelType = str_replace("\\", '\\\\', get_class($this));
//
//        $tableName = $this->getTable();
//
//        $statusTable = 'statuses';
//
//
//        $query->select($tableName.'.*');
//
//        $subSelect = DB::table($statusTable)
//            ->select($statusTable.'.name')
//            ->whereRaw($statusTable.'.model_id = '.$tableName.'.id')
//            ->whereRaw($statusTable.".model_type = '".$modelType."'")
//            ->orderByDesc($statusTable.'.id')
//            ->take(1);
//
//        $query->selectSub($subSelect, 'current_status');
//
//        $query->groupBy($tableName.'.id');
//
//        $query->having('current_status', $status);
//
//        dump($query);
//
//        return $query;
//    }

    public function scopeHasStatus($builder, string $name)
    {
        return $builder->whereIn('id', function ($query) use ($name) {
            $query
                ->select('model_id')
                ->from('statuses')
                ->where('model_type', static::class)
                ->where('name', $name)
                ->whereIn('id', function ($query) use ($name) {
                    $query
                        ->select('model_id')
                        ->from('statuses')
                        ->where('model_type', static::class)
                        ->latest()
                        ->groupBy('model_id');
                });
        });
    }

    public function getStatusAttribute(): string
    {
        return $this->latestStatus();
    }
}
