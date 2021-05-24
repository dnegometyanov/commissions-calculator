<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeekRange;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class PrivateWithdrawRule implements RuleInterface
{
    const WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE   = '0.003';
    const WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT             = '1000';
    const WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT = 3;

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->is(TransactionType::TRANSACTION_TYPE_WITHDRAW);

    }

    /** @inheritDoc */
    public function calculate(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult
    {
        switch (true) {
            case $userCalculationState->isTransactionBeforeWeekRange($transaction):
                throw new \Exception(
                    sprintf('Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                        (string)$transaction->getUuid(),
                        $transaction->getDateTime()->format('Y-m-d H:i:s')
                    ));
            case $userCalculationState->isTransactionAfterWeekRange($transaction):
            default:
                $userCalculationState = new UserCalculationState(
                    0,
                    $transaction->getAmount(),
                    WeekRange::createFromDate($transaction->getDateTime())
                );
                break;
        }

        $limitAmount = Money::of(self::WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT, 'EUR');

        $overLimitAmount = $userCalculationState->getWeeklyTransactionsProcessed() >= self::WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT
            ? $transaction->getAmount()
            : $limitAmount->minus($userCalculationState->getWeeklyAmount());

        if ($overLimitAmount->isNegative()) {
            $overLimitAmount = Money::of(0, 'EUR');
        }

        $commissionAmount = $overLimitAmount->multipliedBy(self::WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE);

        $userCalculationState = new UserCalculationState(
            $userCalculationState->getWeeklyTransactionsProcessed() + 1,
            $userCalculationState->getWeeklyAmount()->plus($transaction->getAmount()),
            WeekRange::createFromDate($transaction->getDateTime())
        );

        return new RuleResult(
            $userCalculationState,
            $commissionAmount
        );
    }
}
