<?php


namespace Spatie\LaravelStatus\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = ['name','explanation','status_id','status_type'];
    protected $table = "statuses";

    public function status()
    {
        return $this->morphTo();
    }
}
