<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CommissionCalculatorTest extends TestCase
{
    /**
     * @dataProvider calculateCommissionForTransactionProvider
     *
     * @param int $userId
     * @param UserType $userType
     * @param int $stateWeeklyTransactionsProcessed
     * @param Money|null $stateWeeklyAmount
     * @param WeekRange|null $stateWeekRange
     * @param DateTimeImmutable $transactionDate
     * @param TransactionType $transactionType
     * @param Money $transactionAmount
     * @param string $expectedCommission
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function testCalculateCommissionForTransaction(
        int $userId,
        UserType $userType,
        int $stateWeeklyTransactionsProcessed,
        ?Money $stateWeeklyAmount,
        ?WeekRange $stateWeekRange,
        DateTimeImmutable $transactionDate,
        TransactionType $transactionType,
        Money $transactionAmount,
        string $expectedCommission
    ): void {
        $user = User::create($userId, $userType);

        $transaction = new Transaction(
            Uuid::uuid4(),
            new DateTimeImmutable('2021-01-01 12:00:00'),
            $user,
            TransactionType::deposit(),
            Money::of('100.00', 'EUR')
        );

        $commonDepositRule = new CommonDepositRule();

        $rulesSequence = RulesSequence::createFromArray(
            [
                $commonDepositRule
            ]
        );

        $userCalculationState = new UserCalculationState(
            $stateWeeklyTransactionsProcessed,
            $stateWeeklyAmount,
            $stateWeekRange
        );

        $userCalculationStateRepository = new UserCalculationStateRepositoryDefault();

        $userCalculationStateRepository->persistStateForUserAndTransactionType($user, $userCalculationState, $transaction->getTransactionType());

        $transactionCommissionCalculator = new CommissionCalculator($rulesSequence, $userCalculationStateRepository);

        $commission = $transactionCommissionCalculator->calculateCommissionForTransaction($transaction);

        $this->assertEquals('EUR 0.30', (string)$commission->getAmount());
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
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('100.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.50'
            ],
            'state_empty_transaction_amount_higher_then_weekly_withdrawal_limit'              => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('2000.00', 'EUR'),
                'expectedCommission'               => 'EUR 10.00'
            ],
            'state_has_weekly_amount_total_weekly_amount_lower_then_weekly_withdrawal_limit'  => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('50.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.25'
            ],
            'state_has_weekly_amount_total_weekly_amount_higher_then_weekly_withdrawal_limit' => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => Money::of('900', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
            'state_has_weekly_transactions_count_equal_to_weekly_withdrawal_limit'            => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 3,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
            'state_has_weekly_transactions_count_higher_then_weekly_withdrawal_limit'         => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 4,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeekRange'                   => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
            'state_has_weekly_transactions_count_lowe_then_weekly_withdrawal_limit'           => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 2.50'
            ],
        ];
    }
}
