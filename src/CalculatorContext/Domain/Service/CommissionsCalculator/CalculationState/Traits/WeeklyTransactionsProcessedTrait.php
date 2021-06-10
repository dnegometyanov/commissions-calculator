<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Traits;

trait WeeklyTransactionsProcessedTrait
{
    /**
     * @var int
     */
    private int $weeklyTransactionsProcessed;

    /**
     * @return int
     */
    public function getWeeklyTransactionsProcessed(): int
    {
        return $this->weeklyTransactionsProcessed;
    }
}
