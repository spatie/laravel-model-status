<?php

namespace Spatie\LaravelModelStatus\Exceptions;

use Exception;

class InvalidStatus extends Exception
{
    public function __construct()
    {
        parent::__construct('The status is not valid, check the status or adjust the isValidStatus method. ');
    }
}
