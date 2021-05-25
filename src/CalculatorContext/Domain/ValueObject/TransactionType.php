<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class TransactionType
{
    const TRANSACTION_TYPE_DEPOSIT  = 'deposit';
    const TRANSACTION_TYPE_WITHDRAW = 'withdraw';

    private string $transactionType;

    private function __construct(
        string $transactionType
    )
    {
        $this->transactionType = $transactionType;
    }

    public static function create(string $operationType): TransactionType
    {
        if (!in_array($operationType, self::getTransactionTypes())) {
            throw new Exception(sprintf('Operation type %s is not available', $operationType));
        }

        return new self($operationType);
    }

    public function getValue(): string
    {
        return $this->transactionType;
    }

    public function is(string $operationType): bool
    {
        return $this->transactionType === $operationType;
    }

    public static function getTransactionTypes(): array
    {
        return [
            self::TRANSACTION_TYPE_DEPOSIT,
            self::TRANSACTION_TYPE_WITHDRAW,
        ];
    }
}