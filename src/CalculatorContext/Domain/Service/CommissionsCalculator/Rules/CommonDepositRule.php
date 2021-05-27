<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Exception;

class CommonDepositRule implements RuleInterface
{
    public const DEPOSIT_COMMISSION_PERCENTAGE = '0.003';

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->is(TransactionType::TRANSACTION_TYPE_DEPOSIT);
    }

    /** @inheritDoc */
    public function calculate(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult
    {
        if ($userCalculationState->isTransactionBeforeWeekRange($transaction)) {
            throw new Exception(
                sprintf(
                    'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                    (string)$transaction->getUuid(),
                    $transaction->getDateTime()->format('Y-m-d H:i:s')
                )
            );
        }

        return new RuleResult(
            new UserCalculationState(), // TODO create new modified state
            $transaction->getAmount()->multipliedBy(self::DEPOSIT_COMMISSION_PERCENTAGE, RoundingMode::HALF_UP)
        );
    }
}
