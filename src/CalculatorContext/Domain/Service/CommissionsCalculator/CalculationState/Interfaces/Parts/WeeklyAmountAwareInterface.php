<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\Parts;

use Brick\Money\Money;

interface WeeklyAmountAwareInterface
{
    /**
     * @return Money
     */
    public function getWeeklyAmount(): Money;
}
