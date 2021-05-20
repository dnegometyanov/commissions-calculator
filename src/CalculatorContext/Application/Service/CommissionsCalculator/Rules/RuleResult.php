<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState\UserCalculationState;

class RuleResult
{
    /**
     * @var UserCalculationState
     */
    private UserCalculationState $userCalculationState;

    /**
     * @var Money
     */
    private Money $commissionAmount;

    public function __construct(
        UserCalculationState $userCalculationState,
        Money $commissionAmount
    )
    {

        $this->userCalculationState = $userCalculationState;
        $this->commissionAmount     = $commissionAmount;
    }

    /**
     * @return UserCalculationState
     */
    public function getUserCalculationState(): UserCalculationState
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
