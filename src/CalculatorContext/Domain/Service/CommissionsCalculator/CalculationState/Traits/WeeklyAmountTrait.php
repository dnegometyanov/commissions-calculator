<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Traits;

use Brick\Money\Money;

trait WeeklyAmountTrait
{
    /**
     * @var Money|null
     */
    private ?Money $weeklyAmount;

    /**
     * @return Money
     */
    public function getWeeklyAmount(): Money
    {
        return $this->weeklyAmount;
    }
}
