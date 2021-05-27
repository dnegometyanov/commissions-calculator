<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CommissionCalculatorTest extends TestCase
{
    public function testCalculateCommissionForTransaction(): void
    {
        $user = User::create(1, UserType::create(UserType::USER_TYPE_PRIVATE));

        $transaction = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 12:00:00'),
            $user,
            TransactionType::create(TransactionType::TRANSACTION_TYPE_DEPOSIT),
            Money::of('100.00', 'EUR')
        );

        $commonDepositRule = new CommonDepositRule();

        $rulesSequence = RulesSequence::createFromArray(
            [
                $commonDepositRule
            ]
        );

        $userCalculationStateRepository = new UserCalculationStateRepositoryDefault();

        $transactionCommissionCalculator = new CommissionCalculator($rulesSequence, $userCalculationStateRepository);

        $commission = $transactionCommissionCalculator->calculateCommissionForTransaction($transaction);

        $this->assertEquals('EUR 0.30', (string)$commission->getAmount());
    }
}
