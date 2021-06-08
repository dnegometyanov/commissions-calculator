<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\ExchangeRates;

use Psr\Http\Message\ResponseInterface;

interface ExchangeRatesClientInterface
{
    /**
     * @return ResponseInterface
     */
    public function request(): ResponseInterface;
}
