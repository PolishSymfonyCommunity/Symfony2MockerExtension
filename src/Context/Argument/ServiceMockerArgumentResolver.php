<?php

namespace PSS\Behat\Symfony2MockerExtension\Context\Argument;

use Behat\Behat\Context\Argument\ArgumentResolver;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use ReflectionClass;
use ReflectionMethod;

final class ServiceMockerArgumentResolver implements ArgumentResolver
{
    /**
     * @var ServiceMocker
     */
    private $serviceMocker;

    /**
     * @param ServiceMocker $serviceMocker
     */
    public function __construct(ServiceMocker $serviceMocker)
    {
        $this->serviceMocker = $serviceMocker;
    }

    /**
     * @param ReflectionClass $classReflection
     * @param mixed[]         $arguments
     *
     * @return mixed[]
     */
    public function resolveArguments(ReflectionClass $classReflection, array $arguments)
    {
        if ($constructor = $classReflection->getConstructor()) {
            $arguments = $this->resolveConstructorArguments($constructor, $arguments);
        }

        return $arguments;
    }

    /**
     * @param ReflectionMethod $constructor
     * @param array            $arguments
     *
     * @return array
     */
    private function resolveConstructorArguments(ReflectionMethod $constructor, array $arguments)
    {
        $constructorParameters = $constructor->getParameters();

        foreach ($constructorParameters as $position => $parameter) {
            if ($parameter->getClass() && ServiceMocker::class === $parameter->getClass()->getName()) {
                $arguments[$position] = $this->serviceMocker;
            }
        }

        return $arguments;
    }
}