<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState;

use Commissions\CalculatorContext\Domain\Entity\User;

interface UserCalculationStateRepositoryInterface
{
    public function persistStateForUser(User $user, UserCalculationState $userCalculationState): void;

    public function getStateForUser(User $user): UserCalculationState;
}
