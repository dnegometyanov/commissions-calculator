<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;

class UserCalculationStateRepositoryDefault implements UserCalculationStateRepositoryInterface
{
    /**
     * @var array
     */
    private array $userCalculationStatesGroupedByUser;

    public function __construct(array $userCalculationStates = [])
    {
        $this->userCalculationStatesGroupedByUser = $userCalculationStates;
    }

    public function persistStateForUser(User $user, UserCalculationState $userCalculationState): void
    {
        $this->userCalculationStatesGroupedByUser[$user->getId()] = $userCalculationState;

    }

    public function getStateForUser(User $user): UserCalculationState
    {
        return $this->userCalculationStatesGroupedByUser[$user->getId()]
            ?? new UserCalculationState();
    }
}
