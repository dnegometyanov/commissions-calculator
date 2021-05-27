<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeekRange;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\BusinessWithdrawRule;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BusinessWithdrawalRuleTest extends TestCase
{
    /**
     * @dataProvider calculateProvider
     *
     * @param int $stateWeeklyTransactionsProcessed
     * @param Money|null $stateWeeklyAmount
     * @param WeekRange $stateWeekRange
     * @param DateTimeImmutable $transactionDate
     * @param TransactionType $transactionType
     * @param Money $transactionAmount
     * @param string $expectedCommission
     *
     * @throws Exception
     */
    public function testCalculate(
        int $stateWeeklyTransactionsProcessed,
        ?Money $stateWeeklyAmount,
        ?WeekRange $stateWeekRange,
        DateTimeImmutable $transactionDate,
        TransactionType $transactionType,
        Money $transactionAmount,
        string $expectedCommission
    ): void {
        $user = User::create(1, UserType::create(UserType::USER_TYPE_PRIVATE));

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

        $businessWithdrawalRule = new BusinessWithdrawRule();

        $ruleResult = $businessWithdrawalRule->calculate($transaction, $userCalculationState);

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
            'state_empty_transaction_amount_lower_then_weekly_withdrawal_limit'               => [
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('100.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.50'
            ],
            'state_empty_transaction_amount_higher_then_weekly_withdrawal_limit'              => [
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('2000.00', 'EUR'),
                'expectedCommission'               => 'EUR 10.00'
            ],
            'state_has_weekly_amount_total_weekly_amount_lower_then_weekly_withdrawal_limit'  => [
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('50.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.25'
            ],
            'state_has_weekly_amount_total_weekly_amount_higher_then_weekly_withdrawal_limit' => [
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
            'state_has_weekly_transactions_count_equal_to_weekly_withdrawal_limit'            => [
                'stateWeeklyTransactionsProcessed' => 3,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
            'state_has_weekly_transactions_count_higher_then_weekly_withdrawal_limit'         => [
                'stateWeeklyTransactionsProcessed' => 4,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
            'state_has_weekly_transactions_count_lowe_then_weekly_withdrawal_limit'           => [
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
        ];
    }

    /**
     * @dataProvider calculateProviderException
     *
     * @param int $stateWeeklyTransactionsProcessed
     * @param Money|null $stateWeeklyAmount
     * @param WeekRange $stateWeekRange
     * @param DateTimeImmutable $transactionDate
     * @param TransactionType $transactionType
     * @param Money $transactionAmount
     * @param string $expectedExceptionClass
     * @param string $expectedExceptionMessage
     *
     * @throws Exception
     */
    public function testCalculateException(
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

        $user = User::create(1, UserType::create(UserType::USER_TYPE_PRIVATE));

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

        $businessWithdrawalRule = new BusinessWithdrawRule();

        $businessWithdrawalRule->calculate($transaction, $userCalculationState);
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
                'stateWeeklyTransactionsProcessed' => 3,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2030-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::create(TransactionType::TRANSACTION_TYPE_WITHDRAW),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedExceptionClass'           => Exception::class,
                'expectedExceptionMessage'         => 'Transactions should be sorted in ascending order by date, error for transaction with id 2dc5b876-0cca-4bc8-8e78-1cc904e4f143 and date 2021-01-01 12:00:00'
            ],
        ];
    }
}
