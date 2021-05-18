<?php declare(strict_types=1);

namespace CommissionsTest\Unit\Domain\Entity;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\ValueObject\ClientType;
use Commissions\CalculatorContext\Domain\ValueObject\OperationType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Class TransactionTest
 */
class TransactionTest extends TestCase
{
    public function testCanCreateTransaction(): void
    {
        $user = User::create(1);

        $transaction = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 12:00:00'),
            $user,
            ClientType::create(ClientType::CLIENT_TYPE_PRIVATE),
            OperationType::create(OperationType::OPERATION_TYPE_DEPOSIT),
            Money::of('100.00', 'EUR')
        );

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNotNull($transaction->getUuid());
        $this->assertEquals('2021-01-01 12:00:00', $transaction->getDateTime()->format('Y-m-d H:i:s'));
    }
}