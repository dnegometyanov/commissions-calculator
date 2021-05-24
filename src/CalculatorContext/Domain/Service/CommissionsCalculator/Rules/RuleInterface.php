<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;

interface RuleInterface
{
    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isSuitable(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     * @param UserCalculationState $userCalculationState
     *
     * @return RuleResult
     */
    public function calculate(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult;
}
