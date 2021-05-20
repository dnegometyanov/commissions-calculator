<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules;

use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Entity\Transaction;

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
    public function calculateCommissionAmount(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult;
}
