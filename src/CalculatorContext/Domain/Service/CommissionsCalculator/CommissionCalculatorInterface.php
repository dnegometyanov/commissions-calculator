<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\Transaction;

interface CommissionCalculatorInterface
{
    /**
     * @param Transaction $transaction
     *
     * @return Commission
     */
    public function calculateCommissionForTransaction(Transaction $transaction): Commission;
}
