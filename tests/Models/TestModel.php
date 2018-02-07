<?php

namespace Spatie\ModelStatus\Tests\Models;

use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];
}
