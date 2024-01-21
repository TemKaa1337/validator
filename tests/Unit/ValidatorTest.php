<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Temkaa\SimpleValidator\Validator;
use Tests\Fixture\Stub\ClassInitialized;
use Tests\Fixture\Stub\TestClass;

final class ValidatorTest extends TestCase
{
    public function testValidateSpecificConstraint(): void
    {
        $object = new TestClass();

        $errors = (new Validator())->validate($object, new ClassInitialized(message: 'failed'));
        $this->assertCount(1, $errors);

        foreach ($errors as $error) {
            self::assertEquals('failed', $error->getMessage());
            self::assertEquals('test', $error->getPath());
            self::assertEquals($object, $error->getInvalidValue());
        }

        $errors = (new Validator())->validate($object, [new ClassInitialized(message: 'failed')]);
        $this->assertCount(1, $errors);

        foreach ($errors as $error) {
            self::assertEquals('failed', $error->getMessage());
            self::assertEquals('test', $error->getPath());
            self::assertEquals($object, $error->getInvalidValue());
        }
    }

    public function testValidateWithoutConstraints(): void
    {
        $object = new class {};
        $errors = (new Validator())->validate($object);
        self::assertEmpty($errors);
    }
}
