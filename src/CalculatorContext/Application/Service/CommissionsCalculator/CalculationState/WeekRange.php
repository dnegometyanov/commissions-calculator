<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState;

use DateTimeImmutable;

class WeekRange
{
    private ?DateTimeImmutable $dateWeekStart;
    private ?DateTimeImmutable $dateWeekEnd;

    private function __construct(
        ?DateTimeImmutable $dateWeekStart = null,
        ?DateTimeImmutable $dateWeekEnd = null
    )
    {
        $this->dateWeekStart = $dateWeekStart;
        $this->dateWeekEnd = $dateWeekEnd;
    }

    public static function createFromDate(DateTimeImmutable $datetime): WeekRange
    {
        return new WeekRange(
            new DateTimeImmutable(date('Y-m-d 00:00:00', strtotime('monday this week', $datetime->getTimestamp()))),
            new DateTimeImmutable(date('Y-m-d 23:59:59', strtotime('sunday this week', $datetime->getTimestamp()))),
        );
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
}
