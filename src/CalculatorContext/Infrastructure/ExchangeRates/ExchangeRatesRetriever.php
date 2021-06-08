<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Infrastructure\ExchangeRates\Exception\ExchangeRatesRequestException;
use Exception;

class ExchangeRatesRetriever implements ExchangeRatesRetrieverInterface
{
    /**
     * @var ExchangeRatesClientInterface
     */
    private ExchangeRatesClientInterface $client;

    /**
     * @var ExchangeRatesFactoryInterface
     */
    private ExchangeRatesFactoryInterface $exchangeRatesFactory;

    public function __construct(
        ExchangeRatesClientInterface $client,
        ExchangeRatesFactoryInterface $exchangeRatesFactory
    ) {
        $this->exchangeRatesFactory = $exchangeRatesFactory;
        $this->client               = $client;
    }

    /**
     * @inheritDoc
     *
     * @return ExchangeRates
     *
     * @throws ExchangeRatesRequestException
     */
    public function retrieve(): ExchangeRates
    {
        try {
            $response = $this->client->request();
        } catch (Exception $e) {
            throw new ExchangeRatesRequestException(
                sprintf('Cannot retrieve exchange rates, ExchangeRateClient error: %s', $e->getMessage())
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new ExchangeRatesRequestException(
                sprintf('Cannot retrieve exchange rates: request failed, status code: %s', $response->getStatusCode())
            );
        }

        return $this->exchangeRatesFactory->create((string)$response->getBody());
    }
}
