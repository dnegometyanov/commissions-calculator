<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class TransactionType
{
    private const TRANSACTION_TYPE_DEPOSIT = 'deposit';
    private const TRANSACTION_TYPE_WITHDRAW = 'withdraw';

    private const TRANSACTION_TYPES = [
        self::TRANSACTION_TYPE_DEPOSIT,
        self::TRANSACTION_TYPE_WITHDRAW,
    ];

    /**
     * @var string
     */
    private string $transactionType;

    /**
     * @param string $transactionType
     */
    private function __construct(string $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     * @param string $operationType
     *
     * @return TransactionType
     *
     * @throws Exception
     */
    public static function of(string $operationType): TransactionType
    {
        if (!in_array($operationType, self::TRANSACTION_TYPES, true)) {
            throw new Exception(sprintf('Operation type %s is not available', $operationType));
        }

        return new self($operationType);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->transactionType;
    }

    /**
     * @return TransactionType
     */
    public static function deposit(): TransactionType
    {
        return new self(self::TRANSACTION_TYPE_DEPOSIT);
    }

    /**
     * @return TransactionType
     */
    public static function withdraw(): TransactionType
    {
        return new self(self::TRANSACTION_TYPE_WITHDRAW);
    }

    /**
     * @param TransactionType $transactionType
     *
     * @return bool
     */
    public function is(TransactionType $transactionType): bool
    {
        return $this->transactionType === $transactionType->getValue();
    }

    /**
     * @return bool
     */
    public function isDeposit(): bool
    {
        return $this->transactionType === self::TRANSACTION_TYPE_DEPOSIT;
    }

    /**
     * @return bool
     */
    public function isWithdraw(): bool
    {
        return $this->transactionType === self::TRANSACTION_TYPE_WITHDRAW;
    }
}
