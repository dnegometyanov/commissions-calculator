<?php declare(strict_types=1);

namespace CommissionsTest\Unit\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Infrastructure\ExchangeRates\ExchangeRatesApiFactory;
use PHPUnit\Framework\TestCase;

class ExchangeRatesApiFactoryTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        // TODO fix hardcode
        $exchangeRatesApiResponse = file_get_contents('/var/code/tests/Unit/Infrastructure/ExchangeRates/Fixture/ExchangeRatesApiResponse.json');

        $exchangeRates = ExchangeRatesApiFactory::create($exchangeRatesApiResponse);

        $this->assertEquals('EUR', $exchangeRates->getBaseCurrencyCode());
        $this->assertEquals('2021-05-25', $exchangeRates->getDateUpdated()->format('Y-m-d'));
        $this->assertEquals('133.181359', $exchangeRates->getRate('JPY'));
        $this->assertNull($exchangeRates->getRate('NOT_EXISTING_CURRENCY_CODE'));
    }
}