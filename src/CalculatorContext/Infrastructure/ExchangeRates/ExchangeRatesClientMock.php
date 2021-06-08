<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Infrastructure\ExchangeRates\Exception\ExchangeRatesRequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ExchangeRatesClientMock implements ExchangeRatesClientInterface
{
    public static string $data = '';

    /**
     * @return ResponseInterface
     *
     * @throws ExchangeRatesRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(): ResponseInterface
    {
        $exchangeRatesApiResponseFixture = file_get_contents(realpath(__DIR__) . '/Fixture/ExchangeRatesApiResponseWthPredefinedValues.json');
        return new Response(200, [], $exchangeRatesApiResponseFixture);
    }
}
