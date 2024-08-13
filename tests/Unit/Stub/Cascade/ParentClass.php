<?php

declare(strict_types=1);

namespace Tests\Unit\Stub\Cascade;

use Temkaa\SimpleValidator\Constraint\Assert\Cascade;

final readonly class ParentClass
{
    public function __construct(
        #[Cascade]
        public ChildClass1 $class1 = new ChildClass1(),
    ) {
    }
}
