<?php

namespace Spatie\LaravelEloquentStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelEloquentStatus\HasStatus;
use Spatie\LaravelEloquentStatus\Models\Status;

class TestModel extends Model
{
    use HasStatus;

    protected $guarded = [];
    public $timestamps = false;
}
