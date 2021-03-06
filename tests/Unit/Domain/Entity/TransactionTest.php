<?php

declare(strict_types=1);

namespace CommissionsTest\Unit\Domain\Entity;

use Brick\Money\Currency;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TransactionTest extends TestCase
{
    /**
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function testCanCreateTransaction(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->method("getId")->willReturn(1);
        $userMock->method("getUserType")->willReturn(UserType::of('private'));

        $transaction = new Transaction(
            Uuid::uuid4(),
            new \DateTimeImmutable('2021-01-01 12:00:00'),
            $userMock,
            TransactionType::of('deposit'),
            Money::of('100.00', 'EUR')
        );

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertTrue($transaction->getCurrency()->is(Currency::of('EUR')));
        $this->assertEquals('EUR', $transaction->getCurrencyCode());
        $this->assertTrue($transaction->getUserType()->is(UserType::of('private')));
        $this->assertEquals('EUR 100.00', (string)$transaction->getAmount());
        $this->assertNotNull($transaction->getUuid());
        $this->assertEquals('2021-01-01 12:00:00', $transaction->getDateTime()->format('Y-m-d H:i:s'));
    }
}
