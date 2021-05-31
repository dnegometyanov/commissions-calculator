<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\CommissionList;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;

class CommissionsCalculator implements CommissionsCalculatorInterface
{
    /**
     * @var CommissionCalculator
     */
    private CommissionCalculator $commissionCalculator;

    /**
     * @param CommissionCalculator $commissionCalculator
     */
    public function __construct(
        CommissionCalculator $commissionCalculator
    ) {
        $this->commissionCalculator = $commissionCalculator;
    }

    /**
     * @inheritDoc
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateCommissions(TransactionList $transactionList, ExchangeRates $exchangeRates): CommissionList
    {
        $commissionsList = new CommissionList();
        foreach ($transactionList->toArray() as $transaction) {
            $transactionCommission = $this->commissionCalculator->calculateCommissionForTransaction($transaction, $exchangeRates);
            $commissionsList->addCommission($transactionCommission);
        }

        return $commissionsList;
    }
}
