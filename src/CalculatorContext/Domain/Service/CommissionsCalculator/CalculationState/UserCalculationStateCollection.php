<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState;

use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class UserCalculationStateCollection
{
    private array $calculationStates;

    private function __construct(array $calculationStates)
    {
        $this->calculationStates = $calculationStates;
    }

    public static function createFromArray(array $calculationStates): UserCalculationStateCollection
    {
        return new self($calculationStates);
    }

    public function getByTransactionType(TransactionType $transactionType): UserCalculationState
    {
        return $this->calculationStates[$transactionType->getValue()] ?? new UserCalculationState();
    }
}
