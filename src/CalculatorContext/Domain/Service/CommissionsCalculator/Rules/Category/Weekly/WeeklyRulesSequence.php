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
     * @var int
     */
    private int $position = 0;

    /**
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): WeeklyRuleInterface
    {
        return $this->rules[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->rules[$this->position]);
    }
}
