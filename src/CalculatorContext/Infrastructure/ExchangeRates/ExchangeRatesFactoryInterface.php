<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;

interface ExchangeRatesFactoryInterface
{
    /**
     * @param string $rawData
     *
     * @return ExchangeRates
     */
    public function create(string $rawData): ExchangeRates;
}
