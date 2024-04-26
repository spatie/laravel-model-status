<?php

namespace Spatie\ModelStatus\Exceptions;

use Exception;

class InvalidEnumType extends Exception
{
    public static function create(string $statusEnumClass): self
    {
        return new self("Enum `{$statusEnumClass}` must be a string backed Enum");
    }
}
