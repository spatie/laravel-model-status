<?php

namespace Spatie\ModelStatus\Tests\Models;

class ValidationTestModel extends TestModel
{
    public function isValidStatus($statusEnum, ?string $reason = null): bool
    {
        if ($statusEnum == TestEnum::InvalidStatus) {
            return false;
        }

        if ($reason === 'InvalidReason') {
            return false;
        }

        return true;
    }
}
