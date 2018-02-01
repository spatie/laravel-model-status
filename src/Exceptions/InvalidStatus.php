<?php

namespace Spatie\LaravelModelStatus\Exceptions;

class InvalidStatus extends \Exception
{
    public static function create($name, $description)
    {
        return new static ('The status: '. $name .' with the description: '. $description .', '.
        'check where you use the setStatus method or adjust the isValidStatus method in the model. ');
    }
}
