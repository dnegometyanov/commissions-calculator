<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeeklyState;

class RuleResult
{
    /**
     * @var WeeklyState
     */
    private WeeklyState $userCalculationState;

    /**
     * @var Money
     */
    private Money $commissionAmount;

    /**
     * @param WeeklyState $userCalculationState
     * @param Money $commissionAmount
     */
    public function __construct(
        WeeklyState $userCalculationState,
        Money $commissionAmount
    ) {
        $this->userCalculationState = $userCalculationState;
        $this->commissionAmount     = $commissionAmount;
    }

    /**
     * @return WeeklyState
     */
    public function getUserCalculationState(): WeeklyState
    {
        return $this->userCalculationState;
    }

    /**
     * @return Money
     */
    public function getCommissionAmount(): Money
    {
        return $this->commissionAmount;
    }
}
