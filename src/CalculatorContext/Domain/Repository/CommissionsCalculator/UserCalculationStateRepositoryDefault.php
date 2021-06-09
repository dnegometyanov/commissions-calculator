<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Repository\CommissionsCalculator;

use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeeklyState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeeklyStateCollection;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class UserCalculationStateRepositoryDefault implements UserCalculationStateRepositoryInterface
{
    /**
     * @var array
     */
    private array $userCalculationStatesGrouped;

    /**
     * @param array $userCalculationStates
     */
    public function __construct(array $userCalculationStates = [])
    {
        $this->userCalculationStatesGrouped = $userCalculationStates;
    }

    /**
     * @inheritDoc
     */
    public function persistStateForUserAndTransactionType(User $user, WeeklyState $userCalculationState, TransactionType $transactionType): void
    {
        if (!isset($this->userCalculationStatesGrouped[$user->getId()])) {
            $this->userCalculationStatesGrouped[$user->getId()] = [];
        }

        $this->userCalculationStatesGrouped[$user->getId()][$transactionType->getValue()] = $userCalculationState;
    }

    /**
     * @inheritDoc
     */
    public function getStateCollectionForUser(User $user): WeeklyStateCollection
    {
        return isset($this->userCalculationStatesGrouped[$user->getId()])
            ? WeeklyStateCollection::createFromArray($this->userCalculationStatesGrouped[$user->getId()])
            : WeeklyStateCollection::createFromArray([]);
    }
}
