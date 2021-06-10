<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly\WeeklyRulesSequence;

class TransactionCommissionCalculator implements TransactionCommissionCalculatorInterface
{
    /**
     * @var WeeklyRulesSequence
     */
    private WeeklyRulesSequence $rulesSequence;

    /**
     * @var UserCalculationStateRepositoryInterface
     */
    private UserCalculationStateRepositoryInterface $userCalculationStateRepository;

    /**
     * @param WeeklyRulesSequence $rulesSequence
     * @param UserCalculationStateRepositoryInterface $userCalculationStateRepository
     */
    public function __construct(
        WeeklyRulesSequence $rulesSequence,
        UserCalculationStateRepositoryInterface $userCalculationStateRepository
    ) {
        $this->rulesSequence                  = $rulesSequence;
        $this->userCalculationStateRepository = $userCalculationStateRepository;
    }

    /**
     * @inheritDoc
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateCommissionForTransaction(Transaction $transaction, ExchangeRates $exchangeRates): Commission
    {
        $transactionCommissionAmount = Money::of('0', $transaction->getAmount()->getCurrency()->getCurrencyCode());

        foreach ($this->rulesSequence as $rule) {
            if ($rule->isSuitable($transaction)) {
                $userCalculationStateCollection = $this->userCalculationStateRepository->getStateCollectionForUser($transaction->getUser());

                $ruleResult = $rule->calculate($transaction, $userCalculationStateCollection, $exchangeRates);

                $this->userCalculationStateRepository->persistStateForUserAndTransactionType(
                    $transaction->getUser(),
                    $ruleResult->getUserCalculationState(),
                    $transaction->getTransactionType(),
                );

                $transactionCommissionAmount = $transactionCommissionAmount->plus($ruleResult->getCommissionAmount());

                break;
            }
        }

        return new Commission($transaction, $transactionCommissionAmount);
    }
}
