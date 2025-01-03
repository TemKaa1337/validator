<?php

declare(strict_types=1);

namespace Service;

use PHPUnit\Framework\TestCase;
use stdClass;
use Temkaa\Validator\Exception\CannotInstantiateValidatorException;
use Temkaa\Validator\Validator;
use Tests\Helper\Stub\ClassWithNonTypedProperty;
use Tests\Helper\Stub\ClassWithNonTypedPropertyWithDefaultValue;
use Tests\Helper\Stub\ClassWithOtherClassProperty;
use Tests\Helper\Stub\ConstraintWithConfigurableHandler;
use function sprintf;

final class InstantiatorTest extends TestCase
{
    public function testDoesNotInstantiateWithNonTypedProperty(): void
    {
        $this->expectException(CannotInstantiateValidatorException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate validator "%s" with argument "class" as it does not have a type.',
                ClassWithNonTypedProperty::class,
            ),
        );

        (new Validator())->validate(
            new stdClass(),
            new ConstraintWithConfigurableHandler(handler: ClassWithNonTypedProperty::class),
        );
    }

    public function testDoesNotInstantiateWithNonTypedPropertyWithDefaultType(): void
    {
        $errors = (new Validator())->validate(
            new stdClass(),
            new ConstraintWithConfigurableHandler(handler: ClassWithNonTypedPropertyWithDefaultValue::class),
        );

        self::assertEmpty($errors);
    }

    public function testDoesNotInstantiateWithOtherClassInjected(): void
    {
        $this->expectException(CannotInstantiateValidatorException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate validator "%s" with argument "property:%s" as it does not exist in container and cannot be resolved.',
                ClassWithOtherClassProperty::class,
                ClassWithNonTypedProperty::class,
            ),
        );

        (new Validator())->validate(
            new stdClass(),
            new ConstraintWithConfigurableHandler(handler: ClassWithOtherClassProperty::class),
        );
    }
}
