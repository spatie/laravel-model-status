<?php

namespace Spatie\LaravelModelStatus\Exceptions;

use Exception;

class InvalidStatus extends Exception
{
    public static function create(string $name)
    {
        return new static ("The status `{$name}` is an invalid status. ");
    }
}
