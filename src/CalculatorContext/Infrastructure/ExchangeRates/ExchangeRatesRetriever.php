<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Exception;
use GuzzleHttp\ClientInterface;

class ExchangeRatesRetriever implements ExchangeRatesRetrieverInterface
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var ExchangeRatesFactoryInterface
     */
    private ExchangeRatesFactoryInterface $exchangeRatesFactory;

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * @var string
     */
    private string $apiKey;

    public function __construct(
        ClientInterface $client,
        ExchangeRatesFactoryInterface $exchangeRatesFactory,
        string $endpoint,
        string $apiKey
    ) {
        $this->exchangeRatesFactory = $exchangeRatesFactory;
        $this->client               = $client;
        $this->endpoint = $endpoint;
        $this->apiKey = $apiKey;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function retrieve(): ExchangeRates
    {
        $response = $this->client->request('GET', sprintf($this->endpoint, $this->apiKey));

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Cannot retrieve exchange rates');
        }

        return $this->exchangeRatesFactory->create((string)$response->getBody());
    }
}
