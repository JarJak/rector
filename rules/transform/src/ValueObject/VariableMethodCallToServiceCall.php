<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class VariableMethodCallToServiceCall
{
    /**
     * @var string
     */
    private $variableType;

    /**
     * @var string
     */
    private $methodName;

    private $argumentValue;

    /**
     * @var string
     */
    private $serviceType;

    /**
     * @var string
     */
    private $serviceMethodName;

    public function __construct(
        string $variableType,
        string $methodName,
        $argumentValue,
        string $serviceType,
        string $serviceMethodName
    ) {
        $this->variableType = $variableType;
        $this->methodName = $methodName;
        $this->argumentValue = $argumentValue;
        $this->serviceType = $serviceType;
        $this->serviceMethodName = $serviceMethodName;
    }

    public function getVariableType(): string
    {
        return $this->variableType;
    }

    public function setVariableType(string $variableType): void
    {
        $this->variableType = $variableType;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function setMethodName(string $methodName): void
    {
        $this->methodName = $methodName;
    }

    public function getArgumentValue()
    {
        return $this->argumentValue;
    }

    public function setArgumentValue($argumentValue): void
    {
        $this->argumentValue = $argumentValue;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    public function setServiceType(string $serviceType): void
    {
        $this->serviceType = $serviceType;
    }

    public function getServiceMethodName(): string
    {
        return $this->serviceMethodName;
    }

    public function setServiceMethodName(string $serviceMethodName): void
    {
        $this->serviceMethodName = $serviceMethodName;
    }
}
