<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
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
    public function calculateCommissionAmount(Transaction $transaction): Money
    {
        return $transaction->getAmount()->multipliedBy(self::DEPOSIT_COMMISSION_PERCENTAGE);
    }
}
