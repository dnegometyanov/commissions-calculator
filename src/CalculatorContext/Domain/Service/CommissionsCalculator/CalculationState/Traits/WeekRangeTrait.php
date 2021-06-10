<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Traits;

use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;

trait WeekRangeTrait
{
    /**
     * @var WeekRange|null
     */
    private ?WeekRange $weekRange;

    /**
     * @return WeekRange|null
     */
    public function getWeekRange(): ?WeekRange
    {
        return $this->weekRange;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isTransactionWithinWeekRange(Transaction $transaction): bool
    {
        return $this->getWeekRange() !== null && $this->getWeekRange()->compareWithDateTime($transaction->getDateTime())->isWithin();
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isTransactionBeforeWeekRange(Transaction $transaction): bool
    {
        return $this->getWeekRange() !== null && $this->getWeekRange()->compareWithDateTime($transaction->getDateTime())->isBefore();
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isTransactionAfterWeekRange(Transaction $transaction): bool
    {
        return $this->getWeekRange() !== null && $this->getWeekRange()->compareWithDateTime($transaction->getDateTime())->isAfter();
    }
}
