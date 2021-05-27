<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject;

class WeekRangeComparison
{
    private const COMPARE_DATETIME_TO_WEEK_RANGE_WITHIN = 'within';
    private const COMPARE_DATETIME_TO_WEEK_RANGE_AFTER  = 'after';
    private const COMPARE_DATETIME_TO_WEEK_RANGE_BEFORE = 'before';

    /**
     * @var string
     */
    private string $value;

    /**
     * @param string $value
     *
     * @throws \Exception
     */
    private function __construct(string $value)
    {
        if (!in_array($value, $this->getWeekRangeComparisonOptions(), true)) {
            throw new \Exception(sprintf('Invalid WeekRangeComparison value %s', $value));
        }

        $this->value = $value;
    }

    /**
     * @return WeekRangeComparison
     */
    public static function within(): WeekRangeComparison
    {
        return new WeekRangeComparison(self::COMPARE_DATETIME_TO_WEEK_RANGE_WITHIN);
    }

    /**
     * @return WeekRangeComparison
     */
    public static function before(): WeekRangeComparison
    {
        return new WeekRangeComparison(self::COMPARE_DATETIME_TO_WEEK_RANGE_BEFORE);
    }

    /**
     * @return WeekRangeComparison
     */
    public static function after(): WeekRangeComparison
    {
        return new WeekRangeComparison(self::COMPARE_DATETIME_TO_WEEK_RANGE_AFTER);
    }

    /**
     * @return bool
     */
    public function isWithin(): bool
    {
        return $this->value === self::COMPARE_DATETIME_TO_WEEK_RANGE_WITHIN;
    }

    /**
     * @return bool
     */
    public function isBefore(): bool
    {
        return $this->value === self::COMPARE_DATETIME_TO_WEEK_RANGE_BEFORE;
    }

    /**
     * @return bool
     */
    public function isAfter(): bool
    {
        return $this->value === self::COMPARE_DATETIME_TO_WEEK_RANGE_AFTER;
    }

    /**
     * @return array|string[]
     */
    private function getWeekRangeComparisonOptions(): array
    {
        return [
            self::COMPARE_DATETIME_TO_WEEK_RANGE_WITHIN,
            self::COMPARE_DATETIME_TO_WEEK_RANGE_AFTER,
            self::COMPARE_DATETIME_TO_WEEK_RANGE_BEFORE,
        ];
    }
}
