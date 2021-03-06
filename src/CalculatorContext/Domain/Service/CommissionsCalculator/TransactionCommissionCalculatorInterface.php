<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;

interface TransactionCommissionCalculatorInterface
{
    /**
     * @param Transaction $transaction
     * @param ExchangeRates $exchangeRates
     *
     * @return Commission
     */
    public function calculateCommissionForTransaction(Transaction $transaction, ExchangeRates $exchangeRates): Commission;
}
