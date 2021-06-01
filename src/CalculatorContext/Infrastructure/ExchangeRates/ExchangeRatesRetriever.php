<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Exception;
use GuzzleHttp\ClientInterface;

class ExchangeRatesRetriever
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var ExchangeRatesFactoryInterface
     */
    private ExchangeRatesFactoryInterface $exchangeRatesFactory;

    public function __construct(ClientInterface $client, ExchangeRatesFactoryInterface $exchangeRatesFactory)
    {
        $this->exchangeRatesFactory = $exchangeRatesFactory;
        $this->client               = $client;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function retrieve(): ExchangeRates
    {
        $response = $this->client->request('GET', 'http://api.exchangeratesapi.io/v1/latest?access_key=a3430aabe13721725b9853a2d2e29bfc&format=1');

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Cannot retrieve exchange rates');
        }

        return $this->exchangeRatesFactory->create((string)$response->getBody());
    }
}
