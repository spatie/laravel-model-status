<?php


namespace Spatie\LaravelStatus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    protected $fillable = ['name','explanation','status_id','status_type'];
    protected $table = "statuses";

    public function status()
    {
        return $this->morphTo();
    }
}
