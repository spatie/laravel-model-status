<?php


namespace Spatie\LaravelEloquentStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelEloquentStatus\HasStatuses;

class ValidationTestModel extends Model
{
    use HasStatuses;

    protected $guarded = [];
    public $timestamps = false;

    public function isValidStatus($status_name, $status_explanation)
    {
        if (count($status_name)<=1 && count($status_explanation)<=1) {
            return false;
        }
        return true;
    }
}
