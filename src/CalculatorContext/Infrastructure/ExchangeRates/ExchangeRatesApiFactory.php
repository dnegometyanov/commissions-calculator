<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Exception;

class ExchangeRatesApiFactory implements ExchangeRatesFactoryInterface
{
    public static function create($rawData): ExchangeRates
    {
        try {
            $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            echo 'Error'; // TODO
        }

        if (
            !isset($data['base']) ||
            !isset($data['date']) ||
            !isset($data['rates']) ||
            !is_array($data['rates'])
        ) {
            throw new Exception('Incorrect raw data format for exchange rates');
        }

        try {
            $dateUpdated = new \DateTimeImmutable($data['date']);
        } catch (Exception $e) {
            echo 'Error creating datetime'; // TODO
        }

        return new ExchangeRates(
            $data['base'],
            $dateUpdated,
            $data['rates']
        );
    }
}
