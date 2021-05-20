<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState;

use Brick\Money\Money;

class UserCalculationState
{
    private int $weeklyTransactionsProcessed;
    private Money $weeklyAmount;

    public function __construct(
        int $weeklyTransactionsProcessed = 0,
        ?Money $weeklyAmount = null
    )
    {
        if ($weeklyAmount === null) {
            $weeklyAmount = Money::zero('EUR');
        }

        $this->weeklyTransactionsProcessed = $weeklyTransactionsProcessed;
        $this->weeklyAmount                = $weeklyAmount;
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
}
