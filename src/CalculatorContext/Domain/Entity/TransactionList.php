<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

class TransactionList
{
    /**
     * @var Transaction[]
     */
    private array $transactions;

    public function __construct()
    {
        $this->transactions = [];
    }

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

    /**
     * @return Transaction[]
     */
    public function toArray(): array
    {
        return $this->transactions;
    }
}
