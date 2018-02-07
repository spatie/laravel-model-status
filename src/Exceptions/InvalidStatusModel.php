<?php

namespace Spatie\ModelStatus\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidStatusModel extends Exception
{
    public static function create(string $model): self
    {
        return new self("The model `{$model}` does not extend the model \Spatie\ModelStatus\Status.");
    }
}
