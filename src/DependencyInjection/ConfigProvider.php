<?php

declare(strict_types=1);

namespace Temkaa\Validator\DependencyInjection;

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Provider\Config\ProviderInterface;
use Temkaa\Validator\Validator;
use Temkaa\Validator\ValidatorInterface;

final readonly class ConfigProvider implements ProviderInterface
{
    public function provide(): Config
    {
        return ConfigBuilder::make()
            ->include(__DIR__.'/../Constraint/Validator')
            ->include(__DIR__.'/../Constraint/ConstraintValidatorInterface.php')
            ->include(__DIR__.'/../Validator.php')
            ->include(__DIR__.'/../ValidatorInterface.php')
            ->bindInterface(ValidatorInterface::class, Validator::class)
            ->build();
    }
}
