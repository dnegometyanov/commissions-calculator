<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Currency;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\TransactionList;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionsCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\FlatPercentageRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition\ConditionTransactionTypeAndUserType;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\WeeklyThresholdPercentageRule;
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
    private const TRANSACTION_UUID_5 = '55555555-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_6 = '66666666-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_7 = '77777777-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_8 = '88888888-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_9 = '99999999-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_10 = '10101010-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_11 = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_12 = 'bbbbbbbb-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TRANSACTION_UUID_13 = 'cccccccc-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

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

        $exchangeRates = new ExchangeRates(
            'EUR',
            new DateTimeImmutable('2021-05-01'),
            [
                'JPY' => '129.53',
                'USD' => '1.1497',
            ]
        );

        $conditionBusinessWithdrawalRule = new ConditionTransactionTypeAndUserType(
            TransactionType::of('withdraw'),
            UserType::of('business'),
        );

        $businessWithdrawRule = new FlatPercentageRule(
            $conditionBusinessWithdrawalRule,
            TransactionType::of('withdraw'),
            Currency::of('EUR'),
            '0.005'
        );

        $conditionCommonDepositRule = new ConditionTransactionTypeAndUserType(
            TransactionType::of('deposit'),
            null,
        );

        $commonDepositRule = new FlatPercentageRule(
            $conditionCommonDepositRule,
            TransactionType::of('withdraw'),
            Currency::of('EUR'),
            '0.0003'
        );

        $conditionPrivateWithdrawalRule = new ConditionTransactionTypeAndUserType(
            TransactionType::of('withdraw'),
            UserType::of('private')
        );

        $privateWithdrawRule = new WeeklyThresholdPercentageRule(
            $conditionPrivateWithdrawalRule,
            TransactionType::of('withdraw'),
            Currency::of('EUR'),
            '0',
            Money::of('1000', 'EUR'),
            3,
            '0.003'
        );

        $rulesSequence = new RulesSequence(
            [
                $commonDepositRule,
                $businessWithdrawRule,
                $privateWithdrawRule,
            ]
        );

        $userCalculationStateRepository = new UserCalculationStateRepositoryDefault();

        $commissionCalculator  = new CommissionCalculator($rulesSequence, $userCalculationStateRepository);
        $commissionsCalculator = new CommissionsCalculator($commissionCalculator);

        $commissionList      = $commissionsCalculator->calculateCommissions($transactionList, $exchangeRates);
        $commissionListArray = $commissionList->toArray();

        // This may like code duplication, but direct assert for each transaction is intentional,
        // so it will be easier to find that transaction failed in case of test failure
        // in comparison to iterating them in cycle
        if (isset($transactionsData[self::TRANSACTION_UUID_1])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_1]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_1]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_2])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_2]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_2]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_3])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_3]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_3]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_4])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_4]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_4]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_5])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_5]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_5]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_6])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_6]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_6]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_7])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_7]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_7]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_8])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_8]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_8]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_9])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_9]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_9]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_10])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_10]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_10]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_11])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_11]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_11]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_12])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_12]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_12]->getAmount());
        }
        if (isset($transactionsData[self::TRANSACTION_UUID_13])) {
            $this->assertEquals($transactionsData[self::TRANSACTION_UUID_13]['expectedCommission'], (string)$commissionListArray[self::TRANSACTION_UUID_13]->getAmount());
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
                        'userType'           => UserType::of('private'),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_3 =>[
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'   => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                ],
            ],
            'transactions_higher_then_weekly_limit_amount_same_user' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('700.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('200.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('500.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.60',
                    ],
                ],
            ],
            'transactions_less_then_weekly_limit_amount_same_user_but_higher_totally_with_other_user' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('700.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('200.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                ],
            ],
            'transactions_less_then_weekly_in_another_currency' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('700.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('310.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                ],
            ],
            'transactions_higher_then_weekly_amount_in_another_currency' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('700.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 2,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('750.00', 'USD'),
                        'expectedCommission' => 'USD 0.90',
                    ],
                ],
            ],
            'transactions_lower_then_weekly_amount_in_another_currency_but_count_is_higher' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_2 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_3 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.00',
                    ],
                    self::TRANSACTION_UUID_4 => [
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_4),
                        'transactionDate'    => new DateTimeImmutable('2021-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('150.00', 'USD'),
                        'expectedCommission' => 'USD 0.45',
                    ],
                ],
            ],

            'given_real_life_scenario' => [
                'transactionsList' => [
                    self::TRANSACTION_UUID_1 => [
//                        2014-12-31,4,private,withdraw,1200.00,EUR
                        'userId'             => 4,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_1),
                        'transactionDate'    => new DateTimeImmutable(' 2014-12-31 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('1200.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.60',
                    ],

                    self::TRANSACTION_UUID_2 => [
//                        2015-01-01,4,private,withdraw,1000.00,EUR
                        'userId'             => 4,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_2),
                        'transactionDate'    => new DateTimeImmutable('2015-01-01 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('1000.00', 'EUR'),
                        'expectedCommission' => 'EUR 3.00',
                    ],
                    self::TRANSACTION_UUID_3 => [
//                        2016-01-05,4,private,withdraw,1000.00,EUR
                        'userId'             => 4,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_3),
                        'transactionDate'    => new DateTimeImmutable('2016-01-05 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('1000.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_4 => [
//                        2016-01-05,1,private,deposit,200.00,EUR
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_4),
                        'transactionDate'    => new DateTimeImmutable('2016-01-05 12:00:00'),
                        'transactionType'    => TransactionType::of('deposit'),
                        'transactionAmount'  => Money::of('200.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.06',
                    ],
                    self::TRANSACTION_UUID_5 => [
//                        2016-01-06,2,business,withdraw,300.00,EUR
                        'userId'             => 2,
                        'userType'           => UserType::of('business'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_5),
                        'transactionDate'    => new DateTimeImmutable('2016-01-05 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('300.00', 'EUR'),
                        'expectedCommission' => 'EUR 1.50',
                    ],
                    self::TRANSACTION_UUID_6 => [
//                        2016-01-06,1,private,withdraw,30000,JPY
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_6),
                        'transactionDate'    => new DateTimeImmutable('2016-01-06 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('30000.00', 'JPY'),
                        'expectedCommission' => 'JPY 0',
                    ],
                    self::TRANSACTION_UUID_7 => [
//                        2016-01-07,1,private,withdraw,1000.00,EUR
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_7),
                        'transactionDate'    => new DateTimeImmutable('2016-01-07 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('1000.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.70',
                    ],
                    self::TRANSACTION_UUID_8 => [
//                        2016-01-07,1,private,withdraw,100.00,USD
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_8),
                        'transactionDate'    => new DateTimeImmutable('2016-01-07 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('100.00', 'USD'),
                        'expectedCommission' => 'USD 0.30',
                    ],
                    self::TRANSACTION_UUID_9 => [
//                        2016-01-10,1,private,withdraw,100.00,EUR
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_9),
                        'transactionDate'    => new DateTimeImmutable('2016-01-10 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('100.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.30',
                    ],
                    self::TRANSACTION_UUID_10 => [
//                        2016-01-10,2,business,deposit,10000.00,EUR
                        'userId'             => 2,
                        'userType'           => UserType::of('business'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_10),
                        'transactionDate'    => new DateTimeImmutable('2016-01-10 12:00:00'),
                        'transactionType'    => TransactionType::of('deposit'),
                        'transactionAmount'  => Money::of('10000.00', 'EUR'),
                        'expectedCommission' => 'EUR 3.00',
                    ],
                    self::TRANSACTION_UUID_11 => [
//                        2016-01-10,3,private,withdraw,1000.00,EUR
                        'userId'             => 3,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_11),
                        'transactionDate'    => new DateTimeImmutable('2016-01-10 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('1000.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_12 => [
//                        2016-02-15,1,private,withdraw,300.00,EUR
                        'userId'             => 1,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_12),
                        'transactionDate'    => new DateTimeImmutable('2016-02-15 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('300.00', 'EUR'),
                        'expectedCommission' => 'EUR 0.00',
                    ],
                    self::TRANSACTION_UUID_13 => [
//                        2016-02-19,5,private,withdraw,3000000,JPY
                        'userId'             => 5,
                        'userType'           => UserType::of('private'),
                        'transactionUuid'    => Uuid::fromString(self::TRANSACTION_UUID_13),
                        'transactionDate'    => new DateTimeImmutable('2016-02-19 12:00:00'),
                        'transactionType'    => TransactionType::of('withdraw'),
                        'transactionAmount'  => Money::of('3000000.00', 'JPY'),
                        'expectedCommission' => 'JPY 8612',
                    ],
                ],
            ],
        ];
    }
}
