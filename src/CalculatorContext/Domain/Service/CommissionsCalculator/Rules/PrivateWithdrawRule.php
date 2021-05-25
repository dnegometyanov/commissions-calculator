<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeekRange;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class PrivateWithdrawRule implements RuleInterface
{
    const WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE   = '0.003';
    const WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT             = '1000';
    const WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT = 3;

    /**
     * @var ExchangeRates
     */
    private ExchangeRates $exchangeRates;

    public function __construct(
        ExchangeRates $exchangeRates
    )
    {
        $this->exchangeRates = $exchangeRates;
    }

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->is(TransactionType::TRANSACTION_TYPE_WITHDRAW)
            && $transaction->getUser()->getUserType()->isBusiness();
    }

    /** @inheritDoc */
    public function calculate(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult
    {
//        var_dump('WeeklyRange ' . (string)$userCalculationState->getWeekRange());
//        var_dump('WeeklyAmount1 ' . (string)$userCalculationState->getWeeklyAmount());
//        var_dump('TransactionDate ' . $transaction->getDateTime()->format('Y-m-d H:i:s'));
//        var_dump($userCalculationState->isTransactionWithinWeekRange($transaction));
//        var_dump($userCalculationState->isTransactionAfterWeekRange($transaction));
//        var_dump($userCalculationState->isTransactionBeforeWeekRange($transaction));
//exit;
        switch (true) {
            case $userCalculationState->isTransactionBeforeWeekRange($transaction):
                throw new \Exception(
                    sprintf('Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                        (string)$transaction->getUuid(),
                        $transaction->getDateTime()->format('Y-m-d H:i:s')
                    ));
            case $userCalculationState->isTransactionAfterWeekRange($transaction):
                $userCalculationState = new UserCalculationState(
                    0,
                    Money::of('0', 'EUR'),
                    WeekRange::createFromDate($transaction->getDateTime())
                );
                break;
        }

        $limitAmount = Money::of(self::WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT, 'EUR');
//        var_dump('WeeklyAmount2 ' . (string)$userCalculationState->getWeeklyAmount());
//        var_dump('LimitAmount ' . (string)$limitAmount);
        $overLimitAmount = $userCalculationState->getWeeklyTransactionsProcessed() >= self::WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT
            ? $transaction->getAmount()
            : $userCalculationState->getWeeklyAmount()->plus($transaction->getAmount())->minus($limitAmount);

//        var_dump('OverLimitAmount1 ' . (string)$overLimitAmount);
        if ($overLimitAmount->isNegative()) {
            $overLimitAmount = Money::of(0, 'EUR');
        }

//        var_dump('OverLimitAmount2 ' . (string)$overLimitAmount);
//        exit;

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
