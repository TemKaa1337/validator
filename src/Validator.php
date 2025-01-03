<?php

declare(strict_types=1);

namespace Temkaa\Validator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\ViolationInterface;
use Temkaa\Validator\Constraint\ViolationList;
use Temkaa\Validator\Constraint\ViolationListInterface;
use Temkaa\Validator\Service\Instantiator;
use Temkaa\Validator\Service\RuleCollector;
use Temkaa\Validator\Utils\InputArgumentValidator;
use function is_array;

/**
 * @api
 */
final readonly class Validator implements ValidatorInterface
{
    private InputArgumentValidator $inputArgumentValidator;

    private Instantiator $instantiator;

    private RuleCollector $ruleCollector;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->instantiator = new Instantiator($container);
        $this->ruleCollector = new RuleCollector();
        $this->inputArgumentValidator = new InputArgumentValidator();
    }

    /**
     * @template TConstraint of ConstraintInterface
     *
     * @param iterable<object>|object            $values
     * @param list<TConstraint>|TConstraint|null $constraints
     *
     * @return ViolationListInterface<int, ViolationInterface>
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function validate(
        iterable|object $values,
        array|ConstraintInterface|null $constraints = null,
    ): ViolationListInterface {
        $this->inputArgumentValidator->validateValues($values);

        /** @var list<TConstraint> $formattedConstraints */
        $formattedConstraints = match (true) {
            $constraints === null  => [],
            is_array($constraints) => $constraints,
            default                => [$constraints]
        };

        /** @var ViolationList<int, ViolationInterface> $violations */
        $violations = new ViolationList();

        $rules = $this->ruleCollector->collect($values, $formattedConstraints);

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
