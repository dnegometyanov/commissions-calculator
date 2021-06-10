<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use Iterator;

class TransactionList implements Iterator
{
    /**
     * @var Transaction[]
     */
    private array $transactions;

    public function __construct()
    {
        $this->transactions = [];
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function addTransaction(Transaction $transaction): TransactionList
    {
        $this->transactions[(string)$transaction->getUuid()] = $transaction;

        return $this;
    }

    /**
     * @param string $transactionUuid
     *
     * @return Transaction
     */
    public function findTransaction(string $transactionUuid): Transaction
    {
        return $this->transactions[$transactionUuid];
    }

    public function rewind(): void
    {
        reset($this->transactions);
    }

    public function current(): Transaction
    {
        return current($this->transactions);
    }

    public function key(): string
    {
        return key($this->transactions);
    }

    public function next(): void
    {
        next($this->transactions);
    }

    public function valid(): bool
    {
        return key($this->transactions) !== null;
    }
}
