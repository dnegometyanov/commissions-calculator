<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;

class UserCalculationState
{
    /**
     * @var int
     */
    private int $weeklyTransactionsProcessed;

    /**
     * @var Money|null
     */
    private ?Money $weeklyAmount;

    /**
     * @var WeekRange|null
     */
    private ?WeekRange $weekRange;

    /**
     * @param int $weeklyTransactionsProcessed
     * @param Money|null $weeklyAmount
     * @param WeekRange|null $weekRange
     */
    public function __construct(
        int $weeklyTransactionsProcessed = 0,
        ?Money $weeklyAmount = null,
        WeekRange $weekRange = null
    ) {
        if ($weeklyAmount === null) {
            $weeklyAmount = Money::zero('EUR');
        }

        $this->weeklyTransactionsProcessed = $weeklyTransactionsProcessed;
        $this->weeklyAmount                = $weeklyAmount;
        $this->weekRange                   = $weekRange;
    }

    /**
     * @return int
     */
    public function getWeeklyTransactionsProcessed(): int
    {
        return $this->weeklyTransactionsProcessed;
    }

    /**
     * @param int $weeklyTransactionsProcessed
     */
    public function setWeeklyTransactionsProcessed(int $weeklyTransactionsProcessed): void
    {
        $this->weeklyTransactionsProcessed = $weeklyTransactionsProcessed;
    }

    /**
     * @return Money
     */
    public function getWeeklyAmount(): Money
    {
        return $this->weeklyAmount;
    }

    /**
     * @param Money $weeklyAmount
     */
    public function setWeeklyAmount(Money $weeklyAmount): void
    {
        $this->weeklyAmount = $weeklyAmount;
    }

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
