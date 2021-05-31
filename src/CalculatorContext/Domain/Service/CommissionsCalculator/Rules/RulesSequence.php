<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

class RulesSequence
{
    /**
     * @var RuleInterface[]
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
     * @return RuleInterface[]
     */
    public function toArray(): array
    {
        return $this->rules;
    }
}
