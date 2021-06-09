<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Traits\WeeklyAmountTrait;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Traits\WeeklyTransactionsProcessedTrait;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Traits\WeekRangeTrait;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;

class WeeklyState implements WeeklyStateInterface
{
    use WeeklyAmountTrait;
    use WeeklyTransactionsProcessedTrait;
    use WeekRangeTrait;
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
}
