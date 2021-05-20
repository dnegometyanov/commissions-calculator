<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState;

use Commissions\CalculatorContext\Domain\Entity\User;

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
