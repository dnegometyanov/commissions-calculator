<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition;

use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;

/**
 * Matches Transaction on exact match of TransactionType and UserType
 * In case one or both of them are null - returns true (suitable)
 */
class ConditionTransactionTypeAndUserType implements ConditionInterface
{
    /**
     * @var TransactionType|null
     */
    private ?TransactionType $transactionType;

    /**
     * @var UserType|null
     */
    private ?UserType $userType;

    public function __construct(
        ?TransactionType $transactionType,
        ?UserType $userType
    ) {
        $this->transactionType = $transactionType;
        $this->userType        = $userType;
    }

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return ($this->transactionType === null || $transaction->getTransactionType()->is($this->transactionType))
            && ($this->userType === null || $transaction->getUserType()->is($this->userType));
    }
}
