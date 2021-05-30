<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\BusinessWithdrawRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\PrivateWithdrawRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CommissionsCalculatorTest extends TestCase
{
    private const TRANSACTION_UUID_1 = '11111111-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_2 = '22222222-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_3 = '33333333-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_4 = '44444444-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

    /**
     * @dataProvider calculateCommissionsProvider
     *
     * @param array $transactionsData
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function testCalculateCommissionForTransactionsList(array $transactionsData): void
    {
        $transactionList = new TransactionList();

        foreach ($transactionsData as $transactionData) {
            $user = User::create($transactionData['userId'], UserType::private());

            $transaction = new Transaction(
                $transactionData['transactionUuid'],
                $transactionData['transactionDate'],
                $user,
                $transactionData['transactionType'],
                $transactionData['transactionAmount'],
            );

            $transactionList->addTransaction($transaction);
        }

        $exchangeRates = new ExchangeRates(
            'EUR',
            new DateTimeImmutable('2021-05-01'),
            [
                'JPY' => '133.181359',
                'USD' => '1.22469',
            ]
        );

        $commonDepositRule = new CommonDepositRule();
        $privateWithdrawRule = new PrivateWithdrawRule($exchangeRates);
        $businessWithdrawRule = new BusinessWithdrawRule();

        $rulesSequence = RulesSequence::createFromArray(
            [
                $commonDepositRule,
                $businessWithdrawRule,
                $privateWithdrawRule,
            ]
        );

        $userCalculationStateRepository = new UserCalculationStateRepositoryDefault();

        $commissionCalculator  = new CommissionCalculator($rulesSequence, $userCalculationStateRepository);
        $commissionsCalculator = new CommissionsCalculator($commissionCalculator);

        $commissionList      = $commissionsCalculator->calculateCommissions($transactionList);
        $commissionListArray = $commissionList->toArray();

        $this->assertEquals($transactionsData[self::TRANSACTION_UUID_1]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_1]->getAmount());
        $this->assertEquals($transactionsData[self::TRANSACTION_UUID_2]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_2]->getAmount());
        $this->assertEquals($transactionsData[self::TRANSACTION_UUID_3]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_3]->getAmount());
        if (isset($transactionsData[self::TRANSACTION_UUID_4])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_4]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_4]->getAmount());
        }
    }

    /**
     * @return array|array[]
     *
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateCommissionsProvider(): array
    {
        return [
            'transactions_less_then_weekly_limit_amount' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::private(),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                    self::TRANSACTION_UUID_3 =>[
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                ],
            ],
            'transactions_higher_then_weekly_limit_amount_same_user' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('700.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('200.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('500.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.60'
                    ],
                ],
            ],
            'transactions_less_then_weekly_limit_amount_same_user_but_higher_totally_with_other_user' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('700.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('200.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00'
                    ],
                ],
            ],
            'transactions_less_then_weekly_in_another_currency' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('700.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('310.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                ],
            ],
            'transactions_higher_then_weekly_amount_in_another_currency' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('700.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('750.00', 'USD'),
                        'expectedCommission' => 'USD 0.68'
                    ],
                ],
            ],
            'transactions_lower_then_weekly_amount_in_another_currency_but_count_is_higher' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00'
                    ],
                    self::TRANSACTION_UUID_4 => [
                        'userId'             => 1,
                        'userType'           => UserType::private(),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_4),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::withdraw(),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.45'
                    ],
                ],
            ],
        ];
    }
}
