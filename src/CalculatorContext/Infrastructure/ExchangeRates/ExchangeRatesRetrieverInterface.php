<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;

interface ExchangeRatesRetrieverInterface
{
    /**
     * @return ExchangeRates
     */
    public function retrieve(): ExchangeRates;
}
