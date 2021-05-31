<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Exception;

class PrivateWithdrawRule implements RuleInterface
{
    private const WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE   = '0.003';
    private const WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT             = '1000';
    private const WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT = 3;

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->isWithdraw()
            && $transaction->getUser()->getUserType()->isPrivate();
    }

    /** @inheritDoc */
    public function calculate(
        Transaction $transaction,
        UserCalculationStateCollection $userCalculationStateCollection,
        ExchangeRates $exchangeRates = null
    ): RuleResult {
        $userWithdrawCalculationState = $userCalculationStateCollection->getByTransactionType(TransactionType::withdraw());

        switch (true) {
            case $userWithdrawCalculationState->isTransactionBeforeWeekRange($transaction):
                throw new Exception(
                    sprintf(
                        'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                        (string)$transaction->getUuid(),
                        $transaction->getDateTime()->format('Y-m-d H:i:s')
                    )
                );
            case $userWithdrawCalculationState->isTransactionAfterWeekRange($transaction):
                $userWithdrawCalculationState = new UserCalculationState(
                    0,
                    Money::of('0', 'EUR'),
                    WeekRange::createFromDate($transaction->getDateTime())
                );
                break;
        }

        $limitAmount = Money::of(self::WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT, 'EUR');

        $transactionCurrencyCode = $transaction->getAmount()->getCurrency()->getCurrencyCode();
        $baseCurrencyCode        = 'EUR';

        // If state amount is lower then allowed free amount,
        // then we need decrease new transaction paid amount by this delta,
        // otherwise, we take commission from whole new transaction
        $stateLimitDelta = $limitAmount->minus($userWithdrawCalculationState->getWeeklyAmount());
        if ($stateLimitDelta->isNegative()) {
            $stateLimitDelta = Money::of('0', 'EUR');
        }

        if ($transactionCurrencyCode === $baseCurrencyCode) {
            $transactionAmountBaseCurrency = $transaction->getAmount();

            $overLimitAmount =
                $userWithdrawCalculationState->getWeeklyTransactionsProcessed() >= self::WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT
                    ? $transaction->getAmount()
                    : $transactionAmountBaseCurrency->minus($stateLimitDelta);
        } else {
            $exchangeRate = $exchangeRates->getRate($transactionCurrencyCode) ?? null;
            if ($exchangeRate === null) {
                throw new Exception(sprintf('Exchange rate for currency code %s not found', $transactionCurrencyCode));
            }

            // TODO inject dependency
            $exchangeRateProvider = new ConfigurableProvider();
            $exchangeRateProvider->setExchangeRate(
                'EUR',
                $transaction->getAmount()->getCurrency()->getCurrencyCode(),
                $exchangeRate
            );
            // TODO inject dependency
            $converter = new CurrencyConverter($exchangeRateProvider); // optionally provide a Context here

            $transactionAmountBaseCurrency = Money::of(
                $transaction->getAmount()->dividedBy($exchangeRate, RoundingMode::HALF_UP)->getAmount(), // TODO think on rounding
                'EUR'
            );

            $overLimitAmountBaseCurrency =
                $userWithdrawCalculationState->getWeeklyTransactionsProcessed() >= self::WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTIONS_COUNT
                    ? $transaction->getAmount()
                    : $transactionAmountBaseCurrency->minus($stateLimitDelta);

            $overLimitAmount = $converter->convert(
                $overLimitAmountBaseCurrency,
                $transaction->getAmount()->getCurrency(),
                RoundingMode::HALF_UP
            );
        }

        if ($overLimitAmount->isNegative()) {
            $overLimitAmount = Money::of(0, $transaction->getAmount()->getCurrency());
        }

        $commissionAmount = $overLimitAmount->multipliedBy(
            self::WITHDRAW_PRIVATE_COMMON_COMMISSION_PERCENTAGE,
            RoundingMode::HALF_UP
        );

        $userWithdrawCalculationState = new UserCalculationState(
            $userWithdrawCalculationState->getWeeklyTransactionsProcessed() + 1,
            $userWithdrawCalculationState->getWeeklyAmount()->plus($transactionAmountBaseCurrency),
            WeekRange::createFromDate($transaction->getDateTime())
        );

        return new RuleResult(
            $userWithdrawCalculationState,
            $commissionAmount
        );
    }
}
