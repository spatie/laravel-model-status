<?php

namespace Spatie\LaravelModelStatus\Tests\Models;

class ValidationTestModel extends TestModel
{
    public function isValidStatus(string $name, string $reason = ''): bool
    {
        if ($name === '') {
            return false;
        }

        if ($reason === '') {
            return false;
        }

        return true;
    }
}
