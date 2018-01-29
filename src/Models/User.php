<?php


namespace Spatie\LaravelEloquentStatus\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password', 'status_id', 'status_type'];
}
