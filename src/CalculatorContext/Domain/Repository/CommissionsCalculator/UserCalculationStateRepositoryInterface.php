<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

interface UserCalculationStateRepositoryInterface
{
    /**
     * @param User $user
     * @param UserCalculationState $userCalculationState
     * @param TransactionType $transactionType
     */
    public function persistStateForUserAndTransactionType(User $user, UserCalculationState $userCalculationState, TransactionType $transactionType): void;

    /**
     * @param User $user
     *
     * @return UserCalculationStateCollection
     */
    public function getStateCollectionForUser(User $user): UserCalculationStateCollection;
}
