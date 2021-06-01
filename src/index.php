<?php

declare(strict_types=1);

namespace Commissions;

use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Infrastructure\ExchangeRates\ExchangeRatesRetriever;
use Commissions\CalculatorContext\Infrastructure\InputData\TransactionsDataRetrieverCSV;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
