<?php declare(strict_types=1);

namespace CommissionsTest\Integration\Application\Service;

use Brick\Money\Money;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
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

        $transactionCommissionCalculator = new CommissionCalculator($rulesSequence);

        $commission = $transactionCommissionCalculator->calculateCommissionForTransaction($transaction);

        $this->assertEquals('EUR 0.30', (string)$commission->getAmount());
    }
}