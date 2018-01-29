<?php


namespace Spatie\LaravelElequentStatus\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public function status(){
        return $this->morphToMany();
    }

}
