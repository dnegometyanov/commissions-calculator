<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Infrastructure\InputData;

use Bcremer\LineReader\LineReader;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class TransactionsDataRetrieverCSV implements TransactionsDataRetrieverInterface
{
    /**
     * @var string
     */
    private string $inputDataPath;

    public function __construct(string $inputDataPath)
    {
        $this->inputDataPath = $inputDataPath;
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $filename): TransactionList
    {
        $path             = sprintf($this->inputDataPath, $filename);
        $transactionsList = new TransactionList();
        foreach (LineReader::readLines($path) as $line) {
            $transactionData = explode(',', $line);
            $transaction = new Transaction(
                Uuid::uuid4(),
                new DateTimeImmutable($transactionData[0]),
                User::create((int)$transactionData[1], UserType::of($transactionData[2])),
                TransactionType::of($transactionData[3]),
                Money::of($transactionData[4], $transactionData[5]),
            );

            $transactionsList->addTransaction($transaction);
        }

        return $transactionsList;
    }
}
