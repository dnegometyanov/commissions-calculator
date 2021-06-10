<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateInterface;

class RuleResult
{
    /**
     * @var WeeklyStateInterface
     */
    private WeeklyStateInterface $userCalculationState;

    /**
     * @var Money
     */
    private Money $commissionAmount;

    /**
     * @param WeeklyStateInterface $userCalculationState
     * @param Money $commissionAmount
     */
    public function __construct(
        WeeklyStateInterface $userCalculationState,
        Money $commissionAmount
    ) {
        $this->userCalculationState = $userCalculationState;
        $this->commissionAmount     = $commissionAmount;
    }

    /**
     * @return WeeklyStateInterface
     */
    public function getUserCalculationState(): WeeklyStateInterface
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
