<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;

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
     * @param UserCalculationStateCollection $userCalculationStateCollection
     * @param ExchangeRates|null $exchangeRates
     *
     * @return RuleResult
     */
    public function calculate(Transaction $transaction, UserCalculationStateCollection $userCalculationStateCollection, ExchangeRates $exchangeRates = null): RuleResult;
}
