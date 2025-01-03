<?php

declare(strict_types=1);

namespace Tests\Helper\Stub\Cascade;

use Temkaa\Validator\Constraint\Assert\Positive;

final class ChildClass3
{
    #[Positive(message: 'ChildClass3 error')]
    public int $int = -1;
}
