<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\Parts;

interface WeeklyTransactionProcessedAwareInterface
{
    /**
     * @return int
     */
    public function getWeeklyTransactionsProcessed(): int;
}
