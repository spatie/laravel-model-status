<?php

namespace Spatie\ModelStatus\Tests\Models;

class ValidationTestModel extends TestModel
{
    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        if ($name === 'InvalidStatus') {
            return false;
        }

        if ($reason === 'InvalidReason') {
            return false;
        }

        return true;
    }
}
