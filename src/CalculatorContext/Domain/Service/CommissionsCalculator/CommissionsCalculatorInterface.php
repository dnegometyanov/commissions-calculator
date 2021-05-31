<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\CommissionList;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;

interface CommissionsCalculatorInterface
{
    /**
     * @param TransactionList $transactionList
     * @param ExchangeRates $exchangeRates
     *
     * @return CommissionList
     */
    public function calculateCommissions(TransactionList $transactionList, ExchangeRates $exchangeRates): CommissionList;
}
