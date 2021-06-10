<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Category\Weekly;

use Iterator;

class WeeklyRulesSequence implements Iterator
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

    public function rewind(): void
    {
        reset($this->rules);
    }

    public function current(): WeeklyRuleInterface
    {
        return current($this->rules);
    }

    public function key(): string
    {
        return key($this->rules);
    }

    public function next(): void
    {
        next($this->rules);
    }

    public function valid(): bool
    {
        return key($this->rules) !== null;
    }
}
