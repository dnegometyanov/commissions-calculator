<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\CommissionList;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;

interface CommissionsCalculatorInterface
{
    public function calculateCommissions(TransactionList $transactionList): CommissionList;
}
