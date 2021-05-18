<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class TransactionType
{
    const TRANSACTION_TYPE_DEPOSIT  = 'deposit';
    const TRANSACTION_TYPE_WITHDRAW = 'withdraw';

    private string $operationType;

    private function __construct(
        string $clientType
    )
    {
        $this->operationType = $clientType;
    }

    public static function create(string $operationType): TransactionType
    {
        if (!in_array($operationType, self::getOperationTypes())) {
            throw new Exception(sprintf('Operation type %s is not available', $operationType));
        }

        return new self($operationType);
    }

    public function getValue(): string
    {
        return $this->operationType;
    }

    public function is(string $operationType): bool
    {
        return $this->operationType === $operationType;
    }

    public static function getOperationTypes(): array
    {
        return [
            self::TRANSACTION_TYPE_DEPOSIT,
            self::TRANSACTION_TYPE_WITHDRAW,
        ];
    }
}