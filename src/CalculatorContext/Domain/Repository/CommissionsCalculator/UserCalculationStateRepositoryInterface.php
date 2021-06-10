<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateCollectionInterface;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\Interfaces\WeeklyStateInterface;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

interface UserCalculationStateRepositoryInterface
{
    /**
     * @param User $user
     * @param WeeklyStateInterface $userCalculationState
     * @param TransactionType $transactionType
     */
    public function persistStateForUserAndTransactionType(User $user, WeeklyStateInterface $userCalculationState, TransactionType $transactionType): void;

    /**
     * @param User $user
     *
     * @return WeeklyStateCollectionInterface
     */
    public function getStateCollectionForUser(User $user): WeeklyStateCollectionInterface;
}
