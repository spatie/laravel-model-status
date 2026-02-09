<?php

namespace Spatie\ModelStatus\Tests\Models;

enum UserStatus: string
{
    case pending = 'pending';
    case accepted = 'accepted';
    case rejected = 'rejected';
    case invalid = 'InvalidStatus';
}
