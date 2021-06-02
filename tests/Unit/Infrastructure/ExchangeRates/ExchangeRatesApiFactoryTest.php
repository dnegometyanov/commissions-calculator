<?php

declare(strict_types=1);

namespace CommissionsTest\Unit\Infrastructure\ExchangeRates;

use Brick\Money\Currency;
use Commissions\CalculatorContext\Infrastructure\ExchangeRates\ExchangeRatesApiFactory;
use PHPUnit\Framework\TestCase;

class ExchangeRatesApiFactoryTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        // 'AED currency rate was deleted from fixture to check non-existing case
        $exchangeRatesApiResponse = file_get_contents(realpath(__DIR__) . '/Fixture/ExchangeRatesApiResponse.json');

        $exchangeRatesApiFactory = new ExchangeRatesApiFactory();

        $exchangeRates = $exchangeRatesApiFactory->create($exchangeRatesApiResponse);

        $this->assertEquals('EUR', $exchangeRates->getBaseCurrencyCode());
        $this->assertEquals('2021-05-25', $exchangeRates->getDateUpdated()->format('Y-m-d'));
        $this->assertEquals('133.181359', $exchangeRates->getRate(Currency::of('JPY')));
        $this->assertNull($exchangeRates->getRate(Currency::of('AED')));
    }
}
