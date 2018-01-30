<?php

namespace Spatie\LaravelStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelStatus\HasStatuses;

class TestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];
    public $timestamps = false;
}
