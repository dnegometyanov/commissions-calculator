<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CommissionsCalculatorTest extends TestCase
{
    /**
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function testCalculateCommissionForTransactionsList(): void
    {
        $user = User::create(1, UserType::private());

        $transaction1 = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 12:00:00'),
            $user,
            TransactionType::deposit(),
            Money::of('100.00', 'EUR')
        );

        $transaction2 = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 13:00:00'),
            $user,
            TransactionType::deposit(),
            Money::of('150.00', 'EUR')
        );

        $transactionList = new TransactionList();

        $transactionList->addTransaction($transaction1);
        $transactionList->addTransaction($transaction2);

        $commonDepositRule = new CommonDepositRule();

        $rulesSequence = RulesSequence::createFromArray(
            [
                $commonDepositRule
            ]
        );

        $userCalculationStateRepository = new UserCalculationStateRepositoryDefault();

        $commissionCalculator  = new CommissionCalculator($rulesSequence, $userCalculationStateRepository);
        $commissionsCalculator = new CommissionsCalculator($commissionCalculator);

        $commissionList      = $commissionsCalculator->calculateCommissions($transactionList);
        $commissionListArray = $commissionList->toArray();

        $this->assertEquals('EUR 0.30', (string)$commissionListArray[(string)$transaction1->getUuid()]->getAmount());
        $this->assertEquals('EUR 0.45', (string)$commissionListArray[(string)$transaction2->getUuid()]->getAmount());
    }
}
