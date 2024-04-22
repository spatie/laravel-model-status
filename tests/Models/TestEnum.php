<?php

namespace Spatie\ModelStatus\Tests\Models;

enum TestEnum: string
{
    case Pending = "pending";
    case Approved = "approved";
    case Rejected = "rejected";
    case InvalidStatus = "invalid_status";
    case UnusedStatus = "unused_status";
}
