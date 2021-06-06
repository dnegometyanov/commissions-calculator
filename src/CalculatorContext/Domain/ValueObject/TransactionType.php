<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

class TransactionType
{
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
     */
    public static function of(string $operationType): TransactionType
    {
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
     * @param TransactionType $transactionType
     *
     * @return bool
     */
    public function is(TransactionType $transactionType): bool
    {
        return $this->transactionType === $transactionType->getValue();
    }
}
