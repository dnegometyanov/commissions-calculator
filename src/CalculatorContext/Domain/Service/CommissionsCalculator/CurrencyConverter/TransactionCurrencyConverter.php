<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CurrencyConverter;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Exception\ExchangeRateNotFoundException;

class TransactionCurrencyConverter
{
    /**
     * @var ConfigurableProvider
     */
    private ConfigurableProvider $exchangeRateProvider;

    /**
     * @var CurrencyConverter
     */
    private CurrencyConverter $currencyConverter;

    /**
     * @var int
     */
    private int $exchangeRateReversePrecision;

    public function __construct(
        ConfigurableProvider $exchangeRateProvider,
        CurrencyConverter $currencyConverter,
        int $exchangeRateReversePrecision
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->currencyConverter = $currencyConverter;
        $this->exchangeRateReversePrecision = $exchangeRateReversePrecision;
    }

    public function convertTransactionAmount(Money $amount, Currency $baseCurrency, Currency $currencyTo, ExchangeRates $exchangeRates): Money
    {
        /**
         * We have one way exchange rates from Base Currency to Transactions Currency,
         * so to convert from Transaction Currency to base currency, we need to revert it
         */
        if ($amount->getCurrency()->is($baseCurrency)) {
            $exchangeRate = $exchangeRates->getRate($currencyTo) ?? null;
            if ($exchangeRate === null) {
                throw new ExchangeRateNotFoundException(sprintf('Exchange rate for currency code %s not found', $currencyTo));
            }
        } else {
            $exchangeRate = $exchangeRates->getRate($amount->getCurrency()) ?? null;
            if ($exchangeRate === null) {
                throw new ExchangeRateNotFoundException(sprintf('Exchange rate for currency code %s not found', $amount->getCurrency()));
            }
            $exchangeRate = bcdiv('1', $exchangeRate, $this->exchangeRateReversePrecision);
        }

        $this->exchangeRateProvider->setExchangeRate(
            $amount->getCurrency()->getCurrencyCode(),
            $currencyTo->getCurrencyCode(),
            $exchangeRate
        );

        return $this->currencyConverter->convert(
            $amount,
            $currencyTo,
            RoundingMode::HALF_UP
        );
    }
}
