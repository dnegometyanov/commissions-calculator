<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Exception;

class BusinessWithdrawRule implements RuleInterface
{
    private const WITHDRAW_BUSINESS_COMMON_COMMISSION_PERCENTAGE = '0.005';

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->isWithdraw()
            && $transaction->getUser()->getUserType()->isBusiness();
    }

    /** @inheritDoc */
    public function calculate(Transaction $transaction, UserCalculationStateCollection $userCalculationStateCollection): RuleResult
    {
        $userWithdrawCalculationState = $userCalculationStateCollection->getByTransactionType(TransactionType::withdraw());

        if ($userWithdrawCalculationState->isTransactionBeforeWeekRange($transaction)) {
            throw new Exception(
                sprintf(
                    'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                    (string)$transaction->getUuid(),
                    $transaction->getDateTime()->format('Y-m-d H:i:s')
                )
            );
        }

        $commissionAmount = $transaction->getAmount()->multipliedBy(
            self::WITHDRAW_BUSINESS_COMMON_COMMISSION_PERCENTAGE,
            RoundingMode::HALF_UP
        );

        return new RuleResult(
            $userWithdrawCalculationState,
            $commissionAmount
        );
    }
}
