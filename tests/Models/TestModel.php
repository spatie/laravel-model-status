<?php

namespace Spatie\LaravelEloquentStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelEloquentStatus\HasStatuses;
use Spatie\LaravelEloquentStatus\Models\Status;

class TestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];
    public $timestamps = false;
}
