<?php

namespace Spatie\ModelStatus\Tests\Models;

enum TestEnum: string
{
    case PENDING = "pending";
    case APPROVED = "approved";
    case REJECTED = "rejected";
    case INVALID_STATUS = "invalid_status";
    case UNUSED_STATUS = "unused_status";
}
