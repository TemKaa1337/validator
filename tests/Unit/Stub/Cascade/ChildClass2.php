<?php

declare(strict_types=1);

namespace Tests\Unit\Stub\Cascade;

use Temkaa\SimpleValidator\Constraint\Assert\Cascade;
use Temkaa\SimpleValidator\Constraint\Assert\Length;

final class ChildClass2
{
    public function __construct(
        #[Cascade]
        public iterable $iterableOfObjects = [new ChildClass3()],
        #[Length(minLength: 2, minMessage: 'ChildClass2 error')]
        public array $array = [1],
    ) {
    }
}
