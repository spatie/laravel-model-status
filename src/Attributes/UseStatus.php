<?php

namespace Spatie\ModelStatus\Attributes;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
class UseStatus
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        public string $enum,
        public bool $strict = false
    ) {
    }
}
