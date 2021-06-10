<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateCollectionInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeeklyState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Exception\ExchangeRateNotFoundException;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Exception\TransactionsNotSortedException;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition\ConditionInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleResult;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class ThresholdPercentageWeeklyRule implements WeeklyRuleInterface
{
    public const EXCHANGE_RATE_REVERSE_PRECISION = 8;

    /**
     * @var ConditionInterface
     */
    private ConditionInterface $condition;

    /**
     * @var TransactionType
     */
    private TransactionType $stateSelectorByTransactionType;

    /**
     * @var Money
     */
    private Money $thresholdWeeklyAmount;

    /**
     * @var int
     */
    private int $thresholdWeeklyTransactions;

    /**
     * @var string
     */
    private string $exceedingThresholdPercentage;
    /**
     * @var Currency
     */
    private Currency $baseCurrency;

    /**
     * @var string
     */
    private string $withinThresholdPercentage;

    /**
     * @param ConditionInterface $condition
     * @param TransactionType $stateSelectorByTransactionType
     * @param Currency $baseCurrency
     * @param string $commonPercentage
     * @param Money $thresholdWeeklyAmount
     * @param int $thresholdWeeklyTransactions
     * @param string $exceedingThresholdPercentage
     */
    public function __construct(
        ConditionInterface $condition,
        TransactionType $stateSelectorByTransactionType, // to select proper UserCalculationState for the Transaction's type
        Currency $baseCurrency,
        string $commonPercentage,
        Money $thresholdWeeklyAmount,
        int $thresholdWeeklyTransactions,
        string $exceedingThresholdPercentage
    ) {
        $this->baseCurrency                 = $baseCurrency;
        $this->stateSelectorByTransactionType = $stateSelectorByTransactionType;
        $this->withinThresholdPercentage    = $commonPercentage;
        $this->thresholdWeeklyAmount        = $thresholdWeeklyAmount;
        $this->thresholdWeeklyTransactions  = $thresholdWeeklyTransactions;
        $this->exceedingThresholdPercentage = $exceedingThresholdPercentage;
        $this->condition                    = $condition;
    }

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $this->condition->isSuitable($transaction);
    }

    /**
     * @inheritDoc
     *
     * @throws ExchangeRateNotFoundException
     * @throws TransactionsNotSortedException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculate(
        Transaction $transaction,
        WeeklyStateCollectionInterface $userCalculationStateCollection,
        ExchangeRates $exchangeRates = null
    ): RuleResult {
        /**
         * UserCalculationStateCollection is a place to store aggregation states,
         * for example like weekly processed transactions amount amd number,
         * they are grouped by TransactionType and each is per-user entry that is retrieved from repository by user id
         *
         * We pass whole UserCalculationStateCollection to the rule, so for withdrawal rule it contains not only withdrawal data,
         * but other transaction types aggregation data as well - while its not needed for the current rule,
         * as it uses only its TransactionType, but provides better extensibility in case we gonna add some new rules
         * that depend on different TransactionType than current rule
         *
         * Here we get State for the TransactionType this rule uses according to its config
         */
        $userCalculationState = $userCalculationStateCollection->getByTransactionType($this->stateSelectorByTransactionType);

        /**
         * In case UserCalculationState WeekRange is later than current processed transaction date,
         * then something went wrong, as our calculation depends on previous transactions aggregation
         * and need transactions to be in order
         */
        $this->validateTransactionOrder($transaction, $userCalculationState);

        /**
         * If current transaction is newer then UserCalculationState WeekRange,
         * we need to create new UserCalculationState with new WeekRange that matches current transaction
         * to aggregate state for new week
         */
        if ($userCalculationState->isTransactionAfterWeekRange($transaction)) {
            $userCalculationState = new WeeklyState(
                0,
                Money::of('0', $this->baseCurrency),
                WeekRange::createFromDate($transaction->getDateTime())
            );
        }

        /**
         * Amount that is left within the threshold
         */
        $leftOverAmountWithingThresholdBaseCurrency = $this->getLeftOverAmountWithinThresholdInBaseCurrency($userCalculationState);

        /**
         * As this rule has calculation logic with different percentage of commission
         * before and after some threshold conditions,
         * we calculate here amount in base currency that is OVER(higher) then threshold
         */
        $overThresholdAmountInTransactionCurrency = ($transaction->getCurrency()->is($this->baseCurrency))
            ? $this->getOverThresholdAmountSameCurrencies(
                $transaction,
                $userCalculationState,
                $leftOverAmountWithingThresholdBaseCurrency
            )
            : $this->getOverThresholdAmountDifferentCurrencies(
                $transaction,
                $userCalculationState,
                $leftOverAmountWithingThresholdBaseCurrency,
                $exchangeRates
            );

        if ($overThresholdAmountInTransactionCurrency->isNegative()) {
            $overThresholdAmountInTransactionCurrency = Money::of(0, $transaction->getCurrency());
        }

        $transactionAmountBaseCurrency = $this->getTransactionAmountBaseCurrency($transaction, $exchangeRates);

        $commissionAmountOverThreshold = $overThresholdAmountInTransactionCurrency->multipliedBy(
            $this->exceedingThresholdPercentage,
            RoundingMode::UP
        );

        /**
         * Its not required by directly described conditions,
         * but allows to configure non-zero percentage below threshold
         * and calculate commission below threshold, so its an easy way to improve configurability
         */
        $amountWithinThresholdInTransactionCurrency = $transaction->getAmount()->minus($overThresholdAmountInTransactionCurrency);

        $commissionAmountWithinThreshold = $amountWithinThresholdInTransactionCurrency->multipliedBy(
            $this->withinThresholdPercentage,
            RoundingMode::UP
        );

        $commissionAmount = $commissionAmountWithinThreshold->plus($commissionAmountOverThreshold);

        /**
         * Return UserCalculationState with updated Weekly Stats
         */
        $userCalculationState = new WeeklyState(
            $userCalculationState->getWeeklyTransactionsProcessed() + 1,
            $userCalculationState->getWeeklyAmount()->plus($transactionAmountBaseCurrency),
            WeekRange::createFromDate($transaction->getDateTime())
        );

        return new RuleResult(
            $userCalculationState,
            $commissionAmount
        );
    }

    /**
     * @param Transaction $transaction
     * @param WeeklyStateInterface $userWithdrawCalculationState
     *
     * @throws TransactionsNotSortedException
     */
    private function validateTransactionOrder(Transaction $transaction, WeeklyStateInterface $userWithdrawCalculationState): void
    {
        if ($userWithdrawCalculationState->isTransactionBeforeWeekRange($transaction)) {
            throw new TransactionsNotSortedException(
                sprintf(
                    'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                    (string)$transaction->getUuid(),
                    $transaction->getDateTime()->format('Y-m-d H:i:s')
                )
            );
        }
    }

    /**
     * Calculated amount in base currency that is left withing Threshold
     *
     * @param WeeklyStateInterface $userCalculationState
     *
     * @return Money
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    private function getLeftOverAmountWithinThresholdInBaseCurrency(WeeklyStateInterface $userCalculationState): Money
    {
        $amountWithingThresholdBaseCurrency = $this->thresholdWeeklyAmount->minus($userCalculationState->getWeeklyAmount());

        if ($amountWithingThresholdBaseCurrency->isNegative()) {
            $amountWithingThresholdBaseCurrency = Money::of('0', $this->baseCurrency);
        }

        return $amountWithingThresholdBaseCurrency;
    }

    private function convertTransactionAmount(Money $amount, Currency $currencyTo, ExchangeRates $exchangeRates): Money
    {
        /**
         * We have one way exchange rates from Base Currency to Transactions Currency,
         * so to convert from Transaction Currency to base currency, we need to revert it
         */
        if ($amount->getCurrency()->is($this->baseCurrency)) {
            $exchangeRate = $exchangeRates->getRate($currencyTo) ?? null;
            if ($exchangeRate === null) {
                throw new ExchangeRateNotFoundException(sprintf('Exchange rate for currency code %s not found', $currencyTo));
            }
        } else {
            $exchangeRate = $exchangeRates->getRate($amount->getCurrency()) ?? null;
            if ($exchangeRate === null) {
                throw new ExchangeRateNotFoundException(sprintf('Exchange rate for currency code %s not found', $amount->getCurrency()));
            }
            $exchangeRate = bcdiv('1', $exchangeRate, self::EXCHANGE_RATE_REVERSE_PRECISION);
        }

        // TODO inject dependency
        $exchangeRateProvider = new ConfigurableProvider();
        $exchangeRateProvider->setExchangeRate(
            $amount->getCurrency()->getCurrencyCode(),
            $currencyTo->getCurrencyCode(),
            $exchangeRate
        );
        // TODO inject dependency
        $converter = new CurrencyConverter($exchangeRateProvider);

        return $converter->convert(
            $amount,
            $currencyTo,
            RoundingMode::HALF_UP
        );
    }

    private function getTransactionAmountBaseCurrency(Transaction $transaction, ExchangeRates $exchangeRates): Money
    {
        if ($transaction->getCurrency()->is($this->baseCurrency)) {
            return $transaction->getAmount();
        }

        return $this->convertTransactionAmount(
            $transaction->getAmount(),
            $this->baseCurrency,
            $exchangeRates
        );
    }

    /**
     * @param Transaction $transaction
     * @param WeeklyStateInterface $userCalculationState
     * @param Money $amountWithingThresholdBaseCurrency
     *
     * @return Money
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    private function getOverThresholdAmountSameCurrencies(Transaction $transaction, WeeklyStateInterface $userCalculationState, Money $amountWithingThresholdBaseCurrency): Money
    {
        $transactionAmountBaseCurrency = $transaction->getAmount();

        /**
         * If WeeklyTransaction Threshold, then whole transaction is over the threshold,
         * But if Amount Threshold, then only over threshold amount
         */
        return $userCalculationState->getWeeklyTransactionsProcessed() >= $this->thresholdWeeklyTransactions
            ? $transaction->getAmount()
            : $transactionAmountBaseCurrency->minus($amountWithingThresholdBaseCurrency);
    }

    /**
     * @param Transaction $transaction
     * @param WeeklyStateInterface $userCalculationState
     * @param Money $amountWithingThresholdBaseCurrency
     * @param ExchangeRates $exchangeRates
     *
     * @return Money
     *
     * @throws ExchangeRateNotFoundException
     * @throws \Brick\Money\Exception\CurrencyConversionException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    private function getOverThresholdAmountDifferentCurrencies(
        Transaction $transaction,
        WeeklyStateInterface $userCalculationState,
        Money $amountWithingThresholdBaseCurrency,
        ExchangeRates $exchangeRates
    ): Money {
        $transactionAmountBaseCurrency = $this->getTransactionAmountBaseCurrency($transaction, $exchangeRates);

        $overThresholdAmountBaseCurrency =
            $userCalculationState->getWeeklyTransactionsProcessed() >= $this->thresholdWeeklyTransactions
                ? $transaction->getAmount()
                : $transactionAmountBaseCurrency->minus($amountWithingThresholdBaseCurrency);

        return $this->convertTransactionAmount(
            $overThresholdAmountBaseCurrency,
            $transaction->getCurrency(),
            $exchangeRates
        );
    }
}
