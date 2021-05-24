<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class CommonDepositRule implements RuleInterface
{
    const DEPOSIT_COMMISSION_PERCENTAGE = '0.003';

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->is(TransactionType::TRANSACTION_TYPE_DEPOSIT);

    }

    /** @inheritDoc */
    public function calculateCommissionAmount(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult
    {
        return new RuleResult(
            new UserCalculationState(), // TODO create new modified state
            $transaction->getAmount()->multipliedBy(self::DEPOSIT_COMMISSION_PERCENTAGE)
        );
    }
}
