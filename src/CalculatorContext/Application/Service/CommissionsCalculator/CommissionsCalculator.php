<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\RuleInterface;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\CommissionList;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;

class CommissionsCalculator implements CommissionsCalculatorInterface
{
    /**
     * @var RulesSequence
     */
    private RulesSequence $rulesSequence;

    public function __construct(
        RulesSequence $rulesSequence
    )
    {
        $this->rulesSequence = $rulesSequence;
    }

    public function calculateCommissions(TransactionList $transactionList): CommissionList
    {
        $commissionsList = new CommissionList();
        /** @var Transaction $transaction */
        foreach ($transactionList as $transaction) {
            $transactionCommission = $this->calculateCommissionForTransaction($transaction);
            $commissionsList->addCommission($transactionCommission);
        }

        return $commissionsList;
    }

    public function calculateCommissionForTransaction(Transaction $transaction): Commission
    {
        /** @var RuleInterface $rule */
        foreach ($this->rulesSequence as $rule) {
            $transactionCommissionAmount = Money::of('0', $transaction->getAmount()->getCurrency()->getCurrencyCode());
            if ($rule->isSuitable($transaction)) {
                $ruleCommission              = $rule->calculateCommissionAmount($transaction);
                $transactionCommissionAmount = $transactionCommissionAmount->plus($ruleCommission);
            }

            return new Commission($transaction, $transactionCommissionAmount);
        }
    }
}
