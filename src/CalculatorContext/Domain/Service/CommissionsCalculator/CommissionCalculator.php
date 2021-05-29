<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RulesSequence;

class CommissionCalculator implements CommissionCalculatorInterface
{
    /**
     * @var RulesSequence
     */
    private RulesSequence $rulesSequence;

    /**
     * @var UserCalculationStateRepositoryInterface
     */
    private UserCalculationStateRepositoryInterface $userCalculationStateRepository;

    /**
     * @param RulesSequence $rulesSequence
     * @param UserCalculationStateRepositoryInterface $userCalculationStateRepository
     */
    public function __construct(
        RulesSequence $rulesSequence,
        UserCalculationStateRepositoryInterface $userCalculationStateRepository
    ) {
        $this->rulesSequence                  = $rulesSequence;
        $this->userCalculationStateRepository = $userCalculationStateRepository;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Commission
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateCommissionForTransaction(Transaction $transaction): Commission
    {
        $transactionCommissionAmount = Money::of('0', $transaction->getAmount()->getCurrency()->getCurrencyCode());

        foreach ($this->rulesSequence->toArray() as $rule) {
            if ($rule->isSuitable($transaction)) {
                $userCalculationStateCollection = $this->userCalculationStateRepository->getStateCollectionForUser($transaction->getUser());
//                var_dump($userCalculationStateCollection);
                $ruleResult = $rule->calculate($transaction, $userCalculationStateCollection);

                $this->userCalculationStateRepository->persistStateForUserAndTransactionType(
                    $transaction->getUser(),
                    $ruleResult->getUserCalculationState(),
                    $transaction->getTransactionType(),
                );

//                var_dump($this->userCalculationStateRepository->getStateCollectionForUser($transaction->getUser()));

                $transactionCommissionAmount = $transactionCommissionAmount->plus($ruleResult->getCommissionAmount());
            }
        }

        return new Commission($transaction, $transactionCommissionAmount);
    }
}
