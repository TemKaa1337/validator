<?php

declare(strict_types=1);

namespace Tests\Unit\Stub\Cascade;

use Temkaa\SimpleValidator\Constraint\Assert\Positive;

final class ChildClass3
{
    #[Positive(message: 'ChildClass3 error')]
    public int $int = -1;
}
