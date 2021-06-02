<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use Brick\Money\Currency;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class Transaction
{
    /**
     * @var UuidInterface
     */
    private UuidInterface $uuid;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $dateTime;

    /**
     * @var User
     */
    private User $user;

    /**
     * @var TransactionType
     */
    private TransactionType $transactionType;

    /**
     * @var Money
     */
    private Money $amount;

    public function __construct(
        UuidInterface $uuid,
        DateTimeImmutable $dateTime,
        User $user,
        TransactionType $transactionType,
        Money $amount
    ) {
        $this->uuid            = $uuid;
        $this->dateTime        = $dateTime;
        $this->user            = $user;
        $this->transactionType = $transactionType;
        $this->amount          = $amount;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return UserType
     */
    public function getUserType(): UserType
    {
        return $this->user->getUserType();
    }

    /**
     * @return TransactionType
     */
    public function getTransactionType(): TransactionType
    {
        return $this->transactionType;
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->getAmount()->getCurrency();
    }


    public function getCurrencyCode(): string
    {
        return $this->getAmount()->getCurrency()->getCurrencyCode();
    }
}
