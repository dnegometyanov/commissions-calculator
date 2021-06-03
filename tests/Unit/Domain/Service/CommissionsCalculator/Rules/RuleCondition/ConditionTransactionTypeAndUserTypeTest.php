<?php

declare(strict_types=1);

namespace CommissionsTest\Unit\Domain\Service\CommissionsCalculator\Rules\RuleCondition;

use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition\ConditionTransactionTypeAndUserType;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use PHPUnit\Framework\TestCase;

class ConditionTransactionTypeAndUserTypeTest extends TestCase
{
    /**
     * @dataProvider conditionTransactionTypeAndUserType
     *
     * @param TransactionType $conditionTransactionType
     * @param UserType $conditionUserType
     * @param TransactionType $transactionTransactionType
     * @param UserType $transactionUserType
     * @param bool $isSuitable
     *
     * @throws \Exception
     */
    public function testConditionTransactionTypeAndUserType(
        ?TransactionType $conditionTransactionType,
        ?UserType $conditionUserType,
        TransactionType $transactionTransactionType,
        UserType $transactionUserType,
        bool $isSuitable
    ): void {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method("getTransactionType")->willReturn($transactionTransactionType);
        $transactionMock->method("getUserType")->willReturn($transactionUserType);

        $condition = new ConditionTransactionTypeAndUserType(
            $conditionTransactionType,
            $conditionUserType
        );

        $this->assertEquals($isSuitable, $condition->isSuitable($transactionMock));
    }

    /**
     * @return array|array[]
     */
    public function conditionTransactionTypeAndUserType(): array
    {
        return [
            'private_withdraw' => [
                'conditionTransactionType' => TransactionType::of('withdraw'),
                'conditionUserType'        => UserType::of('private'),
                'transactionTransactionType' => TransactionType::of('withdraw'),
                'transactionUserType'        => UserType::of('private'),
                'isSuitable'          => true,
            ],
            'business_withdraw' => [
                'conditionTransactionType' => TransactionType::of('withdraw'),
                'conditionUserType'        => UserType::of('business'),
                'transactionTransactionType' => TransactionType::of('withdraw'),
                'transactionUserType'        => UserType::of('business'),
                'isSuitable'          => true,
            ],
            'all_deposit_business_transaction' => [
                'conditionTransactionType' => TransactionType::of('deposit'),
                'conditionUserType'        =>  null,
                'transactionTransactionType' => TransactionType::of('deposit'),
                'transactionUserType'        => UserType::of('business'),
                'isSuitable'          => true,
            ],
            'all_deposit_private_transaction' => [
                'conditionTransactionType' => TransactionType::of('deposit'),
                'conditionUserType'        =>  null,
                'transactionTransactionType' => TransactionType::of('deposit'),
                'transactionUserType'        => UserType::of('private'),
                'isSuitable'          => true,
            ],
            'all_deposit_private' => [
                'conditionTransactionType' => null,
                'conditionUserType'        =>  null,
                'transactionTransactionType' => TransactionType::of('deposit'),
                'transactionUserType'        => UserType::of('private'),
                'isSuitable'          => true,
            ],
            'all_deposit_business' => [
                'conditionTransactionType' => null,
                'conditionUserType'        =>  null,
                'transactionTransactionType' => TransactionType::of('deposit'),
                'transactionUserType'        => UserType::of('business'),
                'isSuitable'          => true,
            ],
            'all_withdraw_private' => [
                'conditionTransactionType' => null,
                'conditionUserType'        =>  null,
                'transactionTransactionType' => TransactionType::of('withdraw'),
                'transactionUserType'        => UserType::of('private'),
                'isSuitable'          => true,
            ],
            'all_withdraw_business' => [
                'conditionTransactionType' => null,
                'conditionUserType'        =>  null,
                'transactionTransactionType' => TransactionType::of('withdraw'),
                'transactionUserType'        => UserType::of('business'),
                'isSuitable'          => true,
            ],
        ];
    }
}
