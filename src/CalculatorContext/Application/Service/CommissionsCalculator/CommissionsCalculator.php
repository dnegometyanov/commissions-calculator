<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\CommissionList;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;

class CommissionsCalculator implements CommissionsCalculatorInterface
{
    /**
     * @var CommissionCalculator
     */
    private CommissionCalculator $commissionCalculator;

    public function __construct(
        CommissionCalculator $commissionCalculator
    )
    {
        $this->commissionCalculator = $commissionCalculator;
    }

    public function calculateCommissions(TransactionList $transactionList): CommissionList
    {
        $commissionsList = new CommissionList();
        foreach ($transactionList->toArray() as $transaction) {
            $transactionCommission = $this->commissionCalculator->calculateCommissionForTransaction($transaction);
            $commissionsList->addCommission($transactionCommission);
        }

        return $commissionsList;
    }
}
