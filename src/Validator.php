<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ViolationList;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;
use Temkaa\SimpleValidator\Service\Instantiator;
use Temkaa\SimpleValidator\Service\RuleCollector;
use Temkaa\SimpleValidator\Utils\InputArgumentValidator;

/**
 * @psalm-api
 */
final readonly class Validator implements ValidatorInterface
{
    private Instantiator $instantiator;

    private RuleCollector $ruleCollector;

    public function __construct(
        ?ContainerInterface $container = null,
    ) {
        $this->instantiator = new Instantiator($container);
        $this->ruleCollector = new RuleCollector();
    }

    /**
     * @inheritDoc
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function validate(
        iterable|object $values,
        array|ConstraintInterface|null $constraints = null,
    ): ViolationListInterface {
        InputArgumentValidator::validateValues($values);
        InputArgumentValidator::validateConstraints($constraints);

        $constraints = match (true) {
            $constraints === null  => [],
            is_array($constraints) => $constraints,
            default                => [$constraints]
        };

        $violations = new ViolationList();

        $rules = $this->ruleCollector->collect($values, $constraints);

        foreach ($rules as $rule) {
            [$validatedValue, $constraints] = $rule;

            foreach ($constraints as $constraint) {
                $handler = $this->instantiator->instantiate($constraint->getHandler());

                $handler->validate($validatedValue, $constraint);

                $violations->merge($handler->getViolations());
            }
        }

        return $violations;
    }
}
