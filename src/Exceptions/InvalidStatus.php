<?php

namespace Spatie\ModelStatus\Exceptions;

use Exception;

class InvalidStatus extends Exception
{
    public static function create(string $name): self
    {
        return new self("The status `{$name}` is an invalid status.");
    }
}
