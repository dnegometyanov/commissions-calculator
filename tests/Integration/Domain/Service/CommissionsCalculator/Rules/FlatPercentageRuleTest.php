<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator\Rules;

use Brick\Money\Currency;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\FlatPercentageRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition\ConditionTransactionTypeAndUserType;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class FlatPercentageRuleTest extends TestCase
{
    /**
     * @dataProvider calculateProvider
     *
     * @param TransactionType $stateSelectorByTransactionType
     * @param TransactionType $conditionTransactionType
     * @param UserType|null $conditionUserType
     * @param int $stateWeeklyTransactionsProcessed
     * @param Money|null $stateWeeklyAmount
     * @param WeekRange|null $stateWeekRange
     * @param DateTimeImmutable $transactionDate
     * @param TransactionType $transactionType
     * @param UserType $transactionUserType
     * @param Money $transactionAmount
     * @param string $ruleCommonPercentage
     * @param string $expectedCommission
     *
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     * @throws \Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Exception\TransactionsNotSortedException
     */
    public function testCalculate(
        TransactionType $stateSelectorByTransactionType,
        TransactionType $conditionTransactionType,
        ?UserType $conditionUserType,
        int $stateWeeklyTransactionsProcessed,
        ?Money $stateWeeklyAmount,
        ?WeekRange $stateWeekRange,
        DateTimeImmutable $transactionDate,
        TransactionType $transactionType,
        UserType $transactionUserType,
        Money $transactionAmount,
        string $ruleCommonPercentage,
        string $expectedCommission
    ): void {
        $user = User::create(1, $transactionUserType);

        $transaction = new Transaction(
            Uuid::uuid4(),
            $transactionDate,
            $user,
            $transactionType,
            $transactionAmount
        );

        $userCalculationState = new UserCalculationState(
            $stateWeeklyTransactionsProcessed,
            $stateWeeklyAmount,
            $stateWeekRange
        );

        $userCalculationStateCollection = UserCalculationStateCollection::createFromArray([
            $stateSelectorByTransactionType->getValue() => $userCalculationState,
        ]);

        $conditionWithdrawalRule = new ConditionTransactionTypeAndUserType(
            $conditionTransactionType,
            $conditionUserType,
        );

        $flatPercentageRule = new FlatPercentageRule(
            $conditionWithdrawalRule,
            $stateSelectorByTransactionType,
            Currency::of('EUR'),
            $ruleCommonPercentage
        );

        $ruleResult = $flatPercentageRule->calculate($transaction, $userCalculationStateCollection);

        $this->assertEquals($expectedCommission, (string)$ruleResult->getCommissionAmount());
    }

    /**
     * @return array|array[]
     *
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateProvider(): array
    {
        return [
            // Business Withdraw tests data
            'state_empty_transaction_amount_lower_then_weekly_withdrawal_limit'               => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('100.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 0.50',
            ],
            'state_empty_transaction_amount_higher_then_weekly_withdrawal_limit'              => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('2000.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 10.00',
            ],
            'state_has_weekly_amount_total_weekly_amount_lower_then_weekly_withdrawal_limit'  => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('50.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 0.25',
            ],
            'state_has_weekly_amount_total_weekly_amount_higher_then_weekly_withdrawal_limit' => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 2.50',
            ],
            'state_has_weekly_transactions_count_equal_to_weekly_withdrawal_limit'            => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 3,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 2.50',
            ],
            'state_has_weekly_transactions_count_higher_then_weekly_withdrawal_limit'         => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 4,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 2.50',
            ],
            'state_has_weekly_transactions_count_lowe_then_weekly_withdrawal_limit'           => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'EUR 2.50',
            ],
            'non_basic_currency_business_withdrawal'           => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'JPY'),
                'ruleCommonPercentage'             => '0.005',
                'expectedCommission'               => 'JPY 3', // No cents for JPY, so 3 instead of 2.5
            ],
            // Business and Private Deposit tests data
            'business_deposit'           => [
                'stateSelectorByTransactionType'   => TransactionType::of('deposit'),
                'conditionTransactionType'         => TransactionType::of('deposit'),
                'conditionUserType'                => null,
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('deposit'),
                'transactionUserType'              => UserType::of('business'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'ruleCommonPercentage'             => '0.0003',
                'expectedCommission'               => 'EUR 0.15',
            ],
            'private_deposit'           => [
                'stateSelectorByTransactionType'   => TransactionType::of('deposit'),
                'conditionTransactionType'         => TransactionType::of('deposit'),
                'conditionUserType'                => null,
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('deposit'),
                'transactionUserType'              => UserType::of('private'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'ruleCommonPercentage'             => '0.0003',
                'expectedCommission'               => 'EUR 0.15',
            ],
        ];
    }

    /**
     * @dataProvider calculateProviderException
     *
     * @param TransactionType $stateSelectorByTransactionType
     * @param TransactionType $conditionTransactionType
     * @param UserType $conditionUserType
     * @param int $stateWeeklyTransactionsProcessed
     * @param Money|null $stateWeeklyAmount
     * @param WeekRange|null $stateWeekRange
     * @param DateTimeImmutable $transactionDate
     * @param TransactionType $transactionType
     * @param Money $transactionAmount
     * @param string $expectedExceptionClass
     * @param string $expectedExceptionMessage
     *
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     * @throws \Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Exception\TransactionsNotSortedException
     */
    public function testCalculateException(
        TransactionType $stateSelectorByTransactionType,
        TransactionType $conditionTransactionType,
        UserType $conditionUserType,
        int $stateWeeklyTransactionsProcessed,
        ?Money $stateWeeklyAmount,
        ?WeekRange $stateWeekRange,
        DateTimeImmutable $transactionDate,
        TransactionType $transactionType,
        Money $transactionAmount,
        string $expectedExceptionClass,
        string $expectedExceptionMessage
    ): void {
        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $user = User::create(1, UserType::of('private'));

        $transaction = new Transaction(
            Uuid::fromString('2dc5b876-0cca-4bc8-8e78-1cc904e4f143'),
            $transactionDate,
            $user,
            $transactionType,
            $transactionAmount
        );

        $userCalculationState = new UserCalculationState(
            $stateWeeklyTransactionsProcessed,
            $stateWeeklyAmount,
            $stateWeekRange
        );

        $userCalculationStateCollection = UserCalculationStateCollection::createFromArray([
            $stateSelectorByTransactionType->getValue() => $userCalculationState,
        ]);

        $conditionWithdrawalRule = new ConditionTransactionTypeAndUserType(
            $conditionTransactionType,
            $conditionUserType,
        );

        $flatPercentageRule = new FlatPercentageRule(
            $conditionWithdrawalRule,
            $stateSelectorByTransactionType,
            Currency::of('EUR'),
            '0.005'
        );

        $flatPercentageRule->calculate($transaction, $userCalculationStateCollection);
    }

    /**
     * @return array|array[]
     *
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function calculateProviderException(): array
    {
        return [
            'transaction_date_before_current_calculation_state_date' => [
                'stateSelectorByTransactionType'   => TransactionType::of('withdraw'),
                'conditionTransactionType'         => TransactionType::of('withdraw'),
                'conditionUserType'                => UserType::of('business'),
                'stateWeeklyTransactionsProcessed' => 3,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2030-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::of('withdraw'),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedExceptionClass'           => Exception::class,
                'expectedExceptionMessage'         => 'Transactions should be sorted in ascending order by date, error for transaction with id 2dc5b876-0cca-4bc8-8e78-1cc904e4f143 and date 2021-01-01 12:00:00',
            ],
        ];
    }
}
