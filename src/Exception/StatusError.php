<?php

namespace Spatie\LaravelStatus\Exception;

use Exception;
use Throwable;

class StatusError extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
