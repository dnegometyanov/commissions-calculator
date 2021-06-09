<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces;

use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

interface WeeklyStateCollectionInterface
{
    public static function createFromArray(array $calculationStates): WeeklyStateCollectionInterface;

    public function getByTransactionType(TransactionType $transactionType): WeeklyStateInterface;
}
