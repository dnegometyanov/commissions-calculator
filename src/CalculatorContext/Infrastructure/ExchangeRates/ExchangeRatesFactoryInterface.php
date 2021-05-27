<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;

interface ExchangeRatesFactoryInterface
{
    /**
     * @param $rawData
     *
     * @return ExchangeRates
     */
    public static function create(string $rawData): ExchangeRates;
}
