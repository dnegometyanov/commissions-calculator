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
                'conditionTransactionType' => TransactionType::withdraw(),
                'conditionUserType'        => UserType::private(),
                'transactionTransactionType' => TransactionType::of('withdraw'),
                'transactionUserType'        => UserType::private(),
                'isSuitable'          => true,
            ],
        ];
    }
}
