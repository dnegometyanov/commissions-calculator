<?php

declare(strict_types=1);

namespace Commissions;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

const TRANSACTION_UUID_1 = '11111111-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

require 'vendor/autoload.php';

// init service container
$containerBuilder = new ContainerBuilder();

$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));

$loader->load('services.yaml');

try {
    // TODO move further logic to console command class
    // TODO read transactions from CSV
    $transactionList  = new TransactionList();
    $transactionsData = [
        TRANSACTION_UUID_1 => [
            'userId'             => 1,
            'userType'           => UserType::private(),
            'transactionUuid'    => Uuid::fromString(TRANSACTION_UUID_1),
            'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
            'transactionType'    => TransactionType::withdraw(),
            'transactionAmount'  => Money::of('100.00', 'EUR'),
            'expectedCommission' => 'EUR 0.00',
        ],
    ];
    foreach ($transactionsData as $transactionData) {
        $user = User::create($transactionData['userId'], $transactionData['userType']);

        $transaction = new Transaction(
            $transactionData['transactionUuid'],
            $transactionData['transactionDate'],
            $user,
            $transactionData['transactionType'],
            $transactionData['transactionAmount'],
        );

        $transactionList->addTransaction($transaction);
    }

    // TODO request exchange rates from service
    $exchangeRates = new ExchangeRates(
        'EUR',
        new DateTimeImmutable('2021-05-01'),
        [
            'JPY' => '129.53',
            'USD' => '1.1497',
        ]
    );

    /** @var CommissionsCalculator $commissionsCalculator */
    $commissionsCalculator = $containerBuilder->get('commissions.calculator');

    dump($commissionsCalculator->calculateCommissions($transactionList, $exchangeRates));
} catch (\Exception $e) {
    dump($e->getMessage());
}
