<?php

declare(strict_types=1);

namespace Tests\Unit\Stub\Cascade;

use Temkaa\SimpleValidator\Constraint\Assert\Cascade;
use Temkaa\SimpleValidator\Constraint\Assert\Negative;

final class ChildClass1
{
    public function __construct(
        #[Cascade]
        public array $arrayOfObjects = [new ChildClass2()],
        #[Negative(message: 'ChildClass1 error')]
        public float $float = 1.01,
    ) {
    }
}
