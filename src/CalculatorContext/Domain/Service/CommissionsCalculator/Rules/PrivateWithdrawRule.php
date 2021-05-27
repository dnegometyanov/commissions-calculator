<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Exception;

class PrivateWithdrawRule implements RuleInterface
{
    private const WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE   = '0.003';
    private const WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT             = '1000';
    private const WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT = 3;

    /**
     * @var ExchangeRates
     */
    private ExchangeRates $exchangeRates;

    /**
     * @param ExchangeRates $exchangeRates
     */
    public function __construct(
        ExchangeRates $exchangeRates
    ) {
        $this->exchangeRates = $exchangeRates;
    }

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->isWithdraw()
            && $transaction->getUser()->getUserType()->isBusiness();
    }

    /** @inheritDoc */
    public function calculate(Transaction $transaction, UserCalculationState $userCalculationState): RuleResult
    {
        switch (true) {
            case $userCalculationState->isTransactionBeforeWeekRange($transaction):
                throw new Exception(
                    sprintf(
                        'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                        (string)$transaction->getUuid(),
                        $transaction->getDateTime()->format('Y-m-d H:i:s')
                    )
                );
            case $userCalculationState->isTransactionAfterWeekRange($transaction):
                $userCalculationState = new UserCalculationState(
                    0,
                    Money::of('0', 'EUR'),
                    WeekRange::createFromDate($transaction->getDateTime())
                );
                break;
        }

        $limitAmount = Money::of(self::WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT, 'EUR');

        $transactionCurrencyCode = $transaction->getAmount()->getCurrency()->getCurrencyCode();
        $baseCurrencyCode        = 'EUR';

        if ($transactionCurrencyCode === $baseCurrencyCode) {
            $transactionAmountBaseCurrency = $transaction->getAmount();

            $overLimitAmount =
                $userCalculationState->getWeeklyTransactionsProcessed() >= self::WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT
                    ? $transaction->getAmount()
                    : $userCalculationState->getWeeklyAmount()->plus($transactionAmountBaseCurrency)->minus($limitAmount);
        } else {
            $exchangeRate = $this->exchangeRates->getRate($transactionCurrencyCode) ?? null;
            if ($exchangeRate === null) {
                throw new Exception(sprintf('Exchange rate for currency code %s not found', $transactionCurrencyCode));
            }

            $transactionAmountBaseCurrency = Money::of(
                $transaction->getAmount()->dividedBy($exchangeRate, RoundingMode::HALF_UP)->getAmount(), // TODO think on rounding
                'EUR'
            );

            $overLimitAmount =
                $userCalculationState->getWeeklyTransactionsProcessed() >= self::WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT
                    ? $transaction->getAmount()
                    : $userCalculationState->getWeeklyAmount()
                    ->plus($transactionAmountBaseCurrency)
                    ->minus($limitAmount)
                    ->multipliedBy($exchangeRate, RoundingMode::HALF_UP);
        }

        if ($overLimitAmount->isNegative()) {
            $overLimitAmount = Money::of(0, 'EUR');
        }

        $commissionAmount = $overLimitAmount->multipliedBy(
            self::WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE,
            RoundingMode::HALF_UP
        );

        $userCalculationState = new UserCalculationState(
            $userCalculationState->getWeeklyTransactionsProcessed() + 1,
            $userCalculationState->getWeeklyAmount()->plus($transactionAmountBaseCurrency),
            WeekRange::createFromDate($transaction->getDateTime())
        );

        return new RuleResult(
            $userCalculationState,
            $commissionAmount
        );
    }
}
