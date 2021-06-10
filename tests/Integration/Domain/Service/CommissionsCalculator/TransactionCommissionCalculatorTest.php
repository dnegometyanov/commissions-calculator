<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Currency;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeeklyState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CurrencyConverter\TransactionCurrencyConverter;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\TransactionCommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly\FlatPercentageWeeklyRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition\ConditionTransactionTypeAndUserType;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly\WeeklyRulesSequence;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly\ThresholdPercentageWeeklyRule;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TransactionCommissionCalculatorTest extends TestCase
{
    /**
     * @dataProvider calculateCommissionForTransactionProvider
     *
     * @param int $userId
     * @param int $stateWeeklyTransactionsProcessed
     * @param Money|null $stateWeeklyAmount
     * @param WeekRange|null $stateWeekRange
     * @param DateTimeImmutable $transactionDate
     * @param TransactionType $transactionType
     * @param UserType $transactionUserType
     * @param Money $transactionAmount
     * @param string $expectedCommission
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function testCalculateCommissionForTransaction(
        int $userId,
        int $stateWeeklyTransactionsProcessed,
        ?Money $stateWeeklyAmount,
        ?WeekRange $stateWeekRange,
        DateTimeImmutable $transactionDate,
        TransactionType $transactionType,
        UserType $transactionUserType,
        Money $transactionAmount,
        string $expectedCommission
    ): void {
        $user = User::create($userId, $transactionUserType);

        $transaction = new Transaction(
            Uuid::uuid4(),
            $transactionDate,
            $user,
            $transactionType,
            $transactionAmount,
        );

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

        $businessWithdrawRule = new FlatPercentageWeeklyRule(
            $conditionBusinessWithdrawalRule,
            TransactionType::of('withdraw'),
            Currency::of('EUR'),
            '0.005'
        );

        $conditionCommonDepositRule = new ConditionTransactionTypeAndUserType(
            TransactionType::of('deposit'),
            null,
        );

        $commonDepositRule = new FlatPercentageWeeklyRule(
            $conditionCommonDepositRule,
            TransactionType::of('withdraw'),
            Currency::of('EUR'),
            '0.0003'
        );

        $conditionPrivateWithdrawalRule = new ConditionTransactionTypeAndUserType(
            TransactionType::of('withdraw'),
            UserType::of('private')
        );

        $configurableProvider = new ConfigurableProvider();
        $currencyConverter = new CurrencyConverter($configurableProvider);

        $transactionCurrencyConverter = new TransactionCurrencyConverter(
            $configurableProvider,
            $currencyConverter,
            8
        );

        $privateWithdrawRule = new ThresholdPercentageWeeklyRule(
            $conditionPrivateWithdrawalRule,
            TransactionType::of('withdraw'),
            Currency::of('EUR'),
            '0',
            $transactionCurrencyConverter,
            Money::of('1000', 'EUR'),
            3,
            '0.003'
        );

        $rulesSequence = new WeeklyRulesSequence(
            [
                $commonDepositRule,
                $businessWithdrawRule,
                $privateWithdrawRule,
            ]
        );

        $userCalculationState = new WeeklyState(
            $stateWeeklyTransactionsProcessed,
            $stateWeeklyAmount,
            $stateWeekRange
        );

        $userCalculationStateRepository = new UserCalculationStateRepositoryDefault();

        $userCalculationStateRepository->persistStateForUserAndTransactionType($user, $userCalculationState, $transaction->getTransactionType());

        $transactionCommissionCalculator = new TransactionCommissionCalculator($rulesSequence, $userCalculationStateRepository);

        $commission = $transactionCommissionCalculator->calculateCommissionForTransaction($transaction, $exchangeRates);

        $this->assertEquals($expectedCommission, (string)$commission->getAmount());
    }

    /**
     * @return array|array[]
     *
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateCommissionForTransactionProvider(): array
    {
        return [
            'state_empty_transaction_amount_lower_then_weekly_withdrawal_limit'               => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('100.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.00',
            ],
            'state_empty_transaction_amount_higher_then_weekly_withdrawal_limit'              => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('2000.00', 'EUR'),
                'expectedCommission'               => 'EUR 3.00',
            ],
            'state_has_weekly_amount_total_weekly_amount_lower_then_weekly_withdrawal_limit'  => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('50.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.00',
            ],
            'state_has_weekly_amount_total_weekly_amount_higher_then_weekly_withdrawal_limit' => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 1.20',
            ],
            'state_has_weekly_transactions_count_equal_to_weekly_withdrawal_limit'            => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 3,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 1.50',
            ],
            'state_has_weekly_transactions_count_higher_then_weekly_withdrawal_limit'         => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 4,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 1.50',
            ],
            'state_has_weekly_transactions_count_lower_then_weekly_withdrawal_limit'          => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.00',
            ],
            'business_deposit'                                                                => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('deposit'),
                'transactionUserType'              => UserType::of('business'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.15',
            ],
            'private_deposit'                                                                 => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('deposit'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.15',
            ],
            'business_withdraw'                                                               => [
                'userId'                           => 1,
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('business'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50',
            ],
        ];
    }
}
