<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Api;

use Commissions\CalculatorContext\Api\Exception\IncorrectCommandLineArgumentsException;
use Commissions\CalculatorContext\Domain\Entity\CommissionList;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculatorInterface;
use Commissions\CalculatorContext\Infrastructure\ExchangeRates\ExchangeRatesRetrieverInterface;
use Commissions\CalculatorContext\Infrastructure\InputData\TransactionsDataRetrieverInterface;

class CalculateCommissionsConsoleCommand implements CalculateCommissionsConsoleCommandInterface
{
    private const COMMAND_LINE_ARGUMENTS_COUNT = 2;
    /**
     * @var TransactionsDataRetrieverInterface
     */
    private TransactionsDataRetrieverInterface $transactionsDataRetriever;

    /**
     * @var ExchangeRatesRetrieverInterface
     */
    private ExchangeRatesRetrieverInterface $exchangeRatesRetriever;

    /**
     * @var CommissionsCalculatorInterface
     */
    private CommissionsCalculatorInterface $commissionsCalculator;

    public function __construct(
        TransactionsDataRetrieverInterface $transactionsDataRetrieverCSV,
        ExchangeRatesRetrieverInterface $exchangeRatesRetriever,
        CommissionsCalculatorInterface $commissionsCalculator
    ) {
        $this->transactionsDataRetriever = $transactionsDataRetrieverCSV;
        $this->exchangeRatesRetriever    = $exchangeRatesRetriever;
        $this->commissionsCalculator     = $commissionsCalculator;
    }

    public function run(): void
    {
        $args = $_SERVER['argv'];

        if (count($args) !== self::COMMAND_LINE_ARGUMENTS_COUNT) {
            throw new IncorrectCommandLineArgumentsException('Invalid command line arguments.');
        }

        $inputFilename = $args[1];

        $transactionList = $this->transactionsDataRetriever->retrieve($inputFilename);

        $exchangeRates = $this->exchangeRatesRetriever->retrieve();

        $commissionsList = $this->commissionsCalculator->calculateCommissions($transactionList, $exchangeRates);

        $this->displayResult($commissionsList);
    }

    private function displayResult(CommissionList $commissionList): void
    {
        foreach ($commissionList->toArray() as $commission) {
            echo (string)$commission->getAmount()->getAmount() . "\n";
        }
    }
}
