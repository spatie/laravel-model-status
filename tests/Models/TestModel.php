<?php

namespace Spatie\ModelStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;

class TestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];
}
