<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\ValueObject\ClientType;
use Commissions\CalculatorContext\Domain\ValueObject\OperationType;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class Transaction
{
    /**
     * @var UuidInterface
     */
    private UuidInterface $uuid;

    private DateTimeImmutable $dateTime;

    /**
     * @var User
     */
    private User $user;

    /**
     * @var ClientType
     */
    private ClientType $clientType;

    /**
     * @var OperationType
     */
    private OperationType $operationType;

    /**
     * @var Money
     */
    private Money $amount;

    public function __construct(
        UuidInterface $uuid,
        DateTimeImmutable $dateTime,
        User $user,
        ClientType $clientType,
        OperationType $operationType,
        Money $amount
    )
    {
        $this->uuid          = $uuid;
        $this->dateTime      = $dateTime;
        $this->user          = $user;
        $this->clientType    = $clientType;
        $this->operationType = $operationType;
        $this->amount        = $amount;
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
     * @return ClientType
     */
    public function getClientType(): ClientType
    {
        return $this->clientType;
    }

    /**
     * @return OperationType
     */
    public function getOperationType(): OperationType
    {
        return $this->operationType;
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }
}