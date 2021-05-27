<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

class RulesSequence
{
    /**
     * @var RuleInterface[]
     */
    private array $rules;

    private function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Order of rules does matter
     *
     * @param array $rules
     *
     * @return $this
     */
    public static function createFromArray(array $rules): RulesSequence
    {
        return new self($rules);
    }

    /**
     * @return RuleInterface[]
     */
    public function toArray(): array
    {
        return $this->rules;
    }
}
