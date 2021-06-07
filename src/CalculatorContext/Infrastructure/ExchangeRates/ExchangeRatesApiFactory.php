<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Infrastructure\ExchangeRates\Exception\ExchangeRatesDataFormatException;
use Exception;

class ExchangeRatesApiFactory implements ExchangeRatesFactoryInterface
{
    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function create(string $rawData): ExchangeRates
    {
        try {
            $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new ExchangeRatesDataFormatException(sprintf('Incorrect data format for exchange rates: %s', $e->getMessage()));
        }

        if (0 === count(array_diff(['base', 'date', 'rates'], array_keys($data))) && !is_array($data['rates'])) {
            throw new ExchangeRatesDataFormatException('Incorrect data structure for exchange rates.');
        }

        try {
            $dateUpdated = new \DateTimeImmutable($data['date']);
        } catch (Exception $e) {
            throw new ExchangeRatesDataFormatException('Incorrect date format for exchange rates.');
        }

        return new ExchangeRates(
            $data['base'],
            $dateUpdated,
            $data['rates']
        );
    }
}
