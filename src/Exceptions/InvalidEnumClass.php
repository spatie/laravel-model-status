<?php

namespace Spatie\ModelStatus\Exceptions;

use Exception;

class InvalidEnumClass extends Exception
{
    public static function create(string $statusEnum): self
    {
        return new self("The status is not of type $statusEnum");
    }
}
