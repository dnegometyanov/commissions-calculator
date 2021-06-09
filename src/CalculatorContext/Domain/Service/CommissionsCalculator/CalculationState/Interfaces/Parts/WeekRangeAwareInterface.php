<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\Parts;

use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;

interface WeekRangeAwareInterface
{
    /**
     * @return WeekRange|null
     */
    public function getWeekRange(): ?WeekRange;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isTransactionWithinWeekRange(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isTransactionBeforeWeekRange(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isTransactionAfterWeekRange(Transaction $transaction): bool;
}
