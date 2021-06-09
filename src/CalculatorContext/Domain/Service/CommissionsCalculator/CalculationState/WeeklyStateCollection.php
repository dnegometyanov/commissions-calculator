<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState;

use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateCollectionInterface;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class WeeklyStateCollection implements WeeklyStateCollectionInterface
{
    private array $calculationStates;

    private function __construct(array $calculationStates)
    {
        $this->calculationStates = $calculationStates;
    }

    public static function createFromArray(array $calculationStates): WeeklyStateCollection
    {
        return new self($calculationStates);
    }

    public function getByTransactionType(TransactionType $transactionType): WeeklyState
    {
        return $this->calculationStates[$transactionType->getValue()] ?? new WeeklyState();
    }
}
