<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

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
    )
    {
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

    public function getRate(string $currencyCode): ?string
    {
        if (!isset($this->rates[$currencyCode])) {
            return null;
        }

        return (string) $this->rates[$currencyCode];
    }
}