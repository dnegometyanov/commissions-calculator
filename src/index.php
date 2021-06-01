<?php

declare(strict_types=1);

namespace Commissions;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Commission;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use Commissions\CalculatorContext\Infrastructure\ExchangeRates\ExchangeRatesRetriever;
use Commissions\CalculatorContext\Infrastructure\InputData\TransactionsDataRetrieverCSV;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

const TRANSACTION_UUID_1 = '11111111-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

require 'vendor/autoload.php';

define('APPROOT', realpath(__DIR__ . '/../'));

// init service container
$containerBuilder = new ContainerBuilder();

$loader = new YamlFileLoader($containerBuilder, new FileLocator(APPROOT . '/src/config/'));

$loader->load('services.yaml');

try {
    /** @var TransactionsDataRetrieverCSV $transactionsDataRetriever */
    $transactionsDataRetrieverCSV =  $containerBuilder->get('transactions.data.retriever');
    $transactionList = $transactionsDataRetrieverCSV->retrieve('input.csv');

    /** @var ExchangeRatesRetriever $exchangeRatesRetriever */
    $exchangeRatesRetriever = $containerBuilder->get('exchange.rates.retriever');
    $exchangeRates = $exchangeRatesRetriever->retrieve();

    /** @var CommissionsCalculator $commissionsCalculator */
    $commissionsCalculator = $containerBuilder->get('commissions.calculator');
    $commissionsList = $commissionsCalculator->calculateCommissions($transactionList, $exchangeRates);
    foreach ($commissionsList->toArray() as $commission) {
        echo (string)$commission->getAmount()->getAmount() . "\n";
    }
} catch (\Exception $e) {
    echo sprintf('Error while calculating commissions: %s', $e->getMessage());
}
