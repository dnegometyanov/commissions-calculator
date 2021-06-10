<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly;

class WeeklyRulesSequence
{
    /**
     * @var WeeklyRuleInterface[]
     */
    private array $rules;

    /**
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return WeeklyRuleInterface[]
     */
    public function toArray(): array
    {
        return $this->rules;
    }
}
