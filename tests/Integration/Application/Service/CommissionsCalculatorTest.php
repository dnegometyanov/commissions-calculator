<?php declare(strict_types=1);

namespace CommissionsTest\Integration\Application\Service;

use Brick\Money\Money;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CommissionsCalculatorTest extends TestCase
{
    public function testCalculateCommissionForTransactionsList(): void
    {
        $user = User::create(1, UserType::create(UserType::USER_TYPE_PRIVATE));

        $transaction1 = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 12:00:00'),
            $user,
            TransactionType::create(TransactionType::TRANSACTION_TYPE_DEPOSIT),
            Money::of('100.00', 'EUR')
        );

        $transaction2 = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 13:00:00'),
            $user,
            TransactionType::create(TransactionType::TRANSACTION_TYPE_DEPOSIT),
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

        $commissionCalculator = new CommissionCalculator($rulesSequence);

        $commissionsCalculator = new CommissionsCalculator($commissionCalculator);

        $commissionList      = $commissionsCalculator->calculateCommissions($transactionList);
        $commissionListArray = $commissionList->toArray();

        $this->assertEquals('EUR 0.30', (string)$commissionListArray[(string)$transaction1->getUuid()]->getAmount());
        $this->assertEquals('EUR 0.45', (string)$commissionListArray[(string)$transaction2->getUuid()]->getAmount());
    }
}