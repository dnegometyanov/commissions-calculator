<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;

interface UserCalculationStateRepositoryInterface
{
    /**
     * @param User $user
     * @param UserCalculationState $userCalculationState
     */
    public function persistStateForUser(User $user, UserCalculationState $userCalculationState): void;

    /**
     * @param User $user
     *
     * @return UserCalculationState
     */
    public function getStateForUser(User $user): UserCalculationState;
}
