<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateCollectionInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleResult;

interface WeeklyRuleInterface extends RuleInterface
{
    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isSuitable(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     * @param WeeklyStateCollectionInterface $userCalculationStateCollection
     * @param ExchangeRates|null $exchangeRates
     *
     * @return RuleResult
     */
    public function calculate(Transaction $transaction, WeeklyStateCollectionInterface $userCalculationStateCollection, ExchangeRates $exchangeRates = null): RuleResult;
}
