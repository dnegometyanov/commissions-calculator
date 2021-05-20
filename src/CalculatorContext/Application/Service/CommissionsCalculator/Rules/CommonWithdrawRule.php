<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules;

use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class CommonWithdrawRule implements RuleInterface
{
    const WITHDRAW_COMMISSION_PERCENTAGE = '0.003';

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->is(TransactionType::TRANSACTION_TYPE_WITHDRAW);

    }

    /** @inheritDoc */
    public function calculateCommissionAmount(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult
    {
        return new RuleResult(
            new UserCalculationState(), // TODO create new modified state
            $transaction->getAmount()->multipliedBy(self::WITHDRAW_COMMISSION_PERCENTAGE)
        );
    }
}
