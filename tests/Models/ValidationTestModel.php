<?php

namespace Spatie\ModelStatus\Tests\Models;

class ValidationTestModel extends TestModel
{
    public function isValidStatus($statusEnum, ?string $reason = null): bool
    {
        if ($statusEnum == TestEnum::INVALID_STATUS) {
            return false;
        }

        if ($reason === 'InvalidReason') {
            return false;
        }

        return true;
    }
}
