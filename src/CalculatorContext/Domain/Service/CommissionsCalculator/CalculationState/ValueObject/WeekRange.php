<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\ValueObject;

use DateTimeImmutable;

class WeekRange
{
    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $dateWeekStart;

    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $dateWeekEnd;

    /**
     * @param DateTimeImmutable|null $dateWeekStart
     * @param DateTimeImmutable|null $dateWeekEnd
     */
    private function __construct(
        ?DateTimeImmutable $dateWeekStart = null,
        ?DateTimeImmutable $dateWeekEnd = null
    ) {
        $this->dateWeekStart = $dateWeekStart;
        $this->dateWeekEnd   = $dateWeekEnd;
    }

    /**
     * @param DateTimeImmutable $datetime
     *
     * @return WeekRange
     *
     * @throws \Exception
     */
    public static function createFromDate(DateTimeImmutable $datetime): WeekRange
    {
        return new WeekRange(
            new DateTimeImmutable(date('Y-m-d 00:00:00', strtotime('monday this week', $datetime->getTimestamp()))),
            new DateTimeImmutable(date('Y-m-d 23:59:59', strtotime('sunday this week', $datetime->getTimestamp()))),
        );
    }

    /**
     * @param DateTimeImmutable $datetime
     *
     * @return WeekRangeComparison
     */
    public function compareWithDateTime(DateTimeImmutable $datetime): WeekRangeComparison
    {
        switch (true) {
            case $datetime < $this->dateWeekStart:
                return WeekRangeComparison::before();
            case $datetime > $this->dateWeekEnd:
                return WeekRangeComparison::after();
            default:
                return WeekRangeComparison::within();
        }
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateWeekStart(): DateTimeImmutable
    {
        return $this->dateWeekStart;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateWeekEnd(): DateTimeImmutable
    {
        return $this->dateWeekEnd;
    }

    public function is(WeekRange $weekRange): bool
    {
        return $this->dateWeekStart->format('Y-m-d H:i:s') === $weekRange->getDateWeekStart()->format('Y-m-d H:i:s')
            && $this->dateWeekEnd->format('Y-m-d H:i:s') === $weekRange->getDateWeekEnd()->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->dateWeekStart === null or $this->dateWeekEnd === null) {
            return '';
        }

        return sprintf(
            '%s - %s',
            $this->dateWeekStart->format('Y-m-d H:i:s'),
            $this->dateWeekEnd->format('Y-m-d H:i:s')
        );
    }
}
