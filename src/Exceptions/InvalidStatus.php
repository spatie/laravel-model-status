<?php

namespace Spatie\LaravelModelStatus\Exceptions;

class InvalidStatus extends \Exception
{
    public static function create(string $name, string $description)
    {
        return new static ("The status: `{$name}` with the description: `{$description}` ".
        "could not be set, adjust the isValidStatus method in your model. ");
    }
}
