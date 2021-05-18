<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules;

class RulesSequence
{
    /**
     * @var RuleInterface[]
     */
    private array $rules;

    public function __construct()
    {
        $this->rules = [];
    }

    /**
     * Order of rules does matter
     *
     * @param array $rules
     *
     * @return $this
     */
    public function createFromArray(array $rules): RulesSequence
    {
        // TODO validate elements type

        $this->rules = $rules;

        return $this;
    }

    /**
     * @return RuleInterface[]
     */
    public function toArray(): array
    {
        return $this->rules;
    }
}
