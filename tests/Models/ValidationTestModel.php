<?php

namespace Spatie\LaravelModelStatus\Tests\Models;

class ValidationTestModel extends TestModel
{
    public function isValidStatus(string $name, string $description = ''): bool
    {
        if ($name === '') {
            return false;
        }

        if ($description === '') {
            return false;
        }

        return true;
    }
}
