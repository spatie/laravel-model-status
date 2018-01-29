<?php

namespace Spatie\LaravelElequentStatus\Tests\Models;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelElequentStatus\HasStatus;

class TestModel extends Model
{
    protected $guarded = [];
    public $timestamps = false;

}