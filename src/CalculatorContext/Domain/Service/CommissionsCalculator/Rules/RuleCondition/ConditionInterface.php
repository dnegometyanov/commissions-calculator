<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition;

use Commissions\CalculatorContext\Domain\Entity\Transaction;

interface ConditionInterface
{
    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isSuitable(Transaction $transaction): bool;
}
