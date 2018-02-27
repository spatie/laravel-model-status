<?php

namespace Spatie\ModelStatus\Exceptions;

use Exception;

class InvalidStatusModel extends Exception
{
    public static function create(string $model): self
    {
        return new self("The model `{$model}` is invalid. A valid model must extend the model \Spatie\ModelStatus\Status.");
    }
}
