<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\Transaction;

class CommissionCalculator implements CommissionCalculatorInterface
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

    public function calculateCommissionForTransaction(Transaction $transaction): Commission
    {
        $transactionCommissionAmount = Money::of('0', $transaction->getAmount()->getCurrency()->getCurrencyCode());

        foreach ($this->rulesSequence->toArray() as $rule) {
            if ($rule->isSuitable($transaction)) {
                $ruleCommission              = $rule->calculateCommissionAmount($transaction);
                $transactionCommissionAmount = $transactionCommissionAmount->plus($ruleCommission);
            }
        }

        return new Commission($transaction, $transactionCommissionAmount);
    }
}
