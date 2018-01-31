<?php

namespace Spatie\LaravelModelStatus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Status extends Model
{
    protected $fillable = ['name', 'description'];
    protected $table = 'statuses';

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
