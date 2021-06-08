<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Infrastructure\ExchangeRates\Exception\ExchangeRatesRequestException;
use Exception;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class ExchangeRatesClient implements ExchangeRatesClientInterface
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @param ClientInterface $client
     * @param string $endpoint
     * @param string $apiKey
     */
    public function __construct(
        ClientInterface $client,
        string $endpoint,
        string $apiKey
    ) {
        $this->client   = $client;
        $this->endpoint = $endpoint;
        $this->apiKey   = $apiKey;
    }

    /**
     * @return ResponseInterface
     *
     * @throws ExchangeRatesRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(): ResponseInterface
    {
        try {
            return $this->client->request('GET', sprintf($this->endpoint, $this->apiKey));
        } catch (Exception $e) {
            throw new ExchangeRatesRequestException(
                sprintf('Cannot retrieve exchange rates, guzzle error: %s', $e->getMessage())
            );
        }
    }
}
