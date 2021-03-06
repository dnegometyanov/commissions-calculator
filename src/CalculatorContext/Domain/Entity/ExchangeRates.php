<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use Brick\Money\Currency;
use DateTimeImmutable;

class ExchangeRates
{
    /**
     * @var string
     */
    private string $baseCurrencyCode;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $dateUpdated;

    /**
     * @var array
     */
    private array $rates;

    public function __construct(
        string $baseCurrencyCode,
        DateTimeImmutable $dateUpdated,
        array $rates
    ) {
        $this->baseCurrencyCode = $baseCurrencyCode;
        $this->dateUpdated      = $dateUpdated;
        $this->rates            = $rates;
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode(): string
    {
        return $this->baseCurrencyCode;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateUpdated(): DateTimeImmutable
    {
        return $this->dateUpdated;
    }

    /**
     * @param Currency $currency
     *
     * @return string|null
     */
    public function getRate(Currency $currency): ?string
    {
        if (!isset($this->rates[$currency->getCurrencyCode()])) {
            return null;
        }

        return (string)$this->rates[$currency->getCurrencyCode()];
    }
}
