<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class TransactionType
{
    public const TRANSACTION_TYPE_DEPOSIT  = 'deposit';
    public const TRANSACTION_TYPE_WITHDRAW = 'withdraw';

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
    public static function create(string $operationType): TransactionType
    {
        if (!in_array($operationType, self::getTransactionTypes(), true)) {
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
     * @param string $operationType
     *
     * @return bool
     */
    public function is(string $operationType): bool
    {
        return $this->transactionType === $operationType;
    }

    /**
     * @return array|string[]
     */
    public static function getTransactionTypes(): array
    {
        return [
            self::TRANSACTION_TYPE_DEPOSIT,
            self::TRANSACTION_TYPE_WITHDRAW,
        ];
    }
}
