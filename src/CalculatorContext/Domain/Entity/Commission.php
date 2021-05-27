<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use Brick\Money\Money;

class Commission
{
    /**
     * @var Transaction
     */
    private Transaction $transaction;

    /**
     * @var Money
     */
    private Money $amount;

    public function __construct(
        Transaction $transaction,
        Money $amount
    ) {
        $this->transaction = $transaction;
        $this->amount      = $amount;
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }
}
