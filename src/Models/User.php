<?php


namespace Spatie\LaravelElequentStatus\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name','email','password','status_id','status_type'];

    public function statuses(){
        return $this->morphMany(Status::class, 'status');
    }
}