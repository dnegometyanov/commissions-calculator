<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\InputData;

use Commissions\CalculatorContext\Domain\Entity\TransactionList;

interface TransactionsDataRetrieverInterface
{
    /**
     * @param string $filename
     *
     * @return TransactionList
     */
    public function retrieve(string $filename): TransactionList;
}
