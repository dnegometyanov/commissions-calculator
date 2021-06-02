<?php

declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator;

use Brick\Money\Currency;
use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject\WeekRange;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\BusinessWithdrawRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\PrivateWithdrawRule;
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

        $commonDepositRule = new CommonDepositRule(
            Currency::of('EUR'),
            '0.0003',
        );

        $privateWithdrawRule = new PrivateWithdrawRule(
            Currency::of('EUR'),
            '0',
            Money::of('1000', 'EUR'),
            3,
            '0.003'
        );

        $businessWithdrawRule = new BusinessWithdrawRule(
            Currency::of('EUR'),
            '0.005',
        );

        $rulesSequence = new RulesSequence(
            [
                $commonDepositRule,
                $businessWithdrawRule,
                $privateWithdrawRule,
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
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 0,
                'stateWeeklyAmount'                => null,
                'stateWeekRange'                   => null,
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('100.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.00',
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
                'expectedCommission'               => 'EUR 3.00',
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
                'expectedCommission'               => 'EUR 0.00',
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
                'expectedCommission'               => 'EUR 1.20',
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
                'expectedCommission'               => 'EUR 1.50',
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
                'expectedCommission'               => 'EUR 1.50',
            ],
            'state_has_weekly_transactions_count_lower_then_weekly_withdrawal_limit'           => [
                'userId'                           => 1,
                'userType'                         => UserType::private(),
                'stateWeeklyTransactionsProcessed' => 1,
                'stateWeeklyAmount'                => Money::of('0', 'EUR'),
                'stateWeeklyRange'                 => WeekRange::createFromDate(new DateTimeImmutable('2021-01-01 12:00:00')),
                'transactionDate'                  => new DateTimeImmutable('2021-01-01 12:00:00'),
                'transactionType'                  => TransactionType::withdraw(),
                'transactionAmount'                => Money::of('500.00', 'EUR'),
                'expectedCommission'               => 'EUR 0.00',
            ],
        ];
    }
}
