<?php

namespace Spatie\LaravelModelStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelModelStatus\HasStatuses;

class TestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];

    public $timestamps = false;
}
