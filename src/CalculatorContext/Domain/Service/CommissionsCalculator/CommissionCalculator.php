<?php declare(strict_types=1);

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

    public function __construct(
        RulesSequence $rulesSequence,
        UserCalculationStateRepositoryInterface $userCalculationStateRepository
    )
    {
        $this->rulesSequence                  = $rulesSequence;
        $this->userCalculationStateRepository = $userCalculationStateRepository;
    }

    public function calculateCommissionForTransaction(Transaction $transaction): Commission
    {
        $transactionCommissionAmount = Money::of('0', $transaction->getAmount()->getCurrency()->getCurrencyCode());

        foreach ($this->rulesSequence->toArray() as $rule) {
            if ($rule->isSuitable($transaction)) {
                $userCalculationState = $this->userCalculationStateRepository->getStateForUser($transaction->getUser());

                $ruleResult = $rule->calculate($transaction, $userCalculationState);

                $this->userCalculationStateRepository->persistStateForUser(
                    $transaction->getUser(),
                    $ruleResult->getUserCalculationState()
                );

                $transactionCommissionAmount = $transactionCommissionAmount->plus($ruleResult->getCommissionAmount());
            }
        }

        return new Commission($transaction, $transactionCommissionAmount);
    }
}
