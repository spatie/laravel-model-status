<?php

namespace Spatie\LaravelEloquentStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelEloquentStatus\HasStatuses;

class TestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];
    public $timestamps = false;
}
