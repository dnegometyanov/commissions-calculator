<?php declare(strict_types=1);

namespace CommissionsTest\Integration\Domain\Service\CommissionsCalculator\CalculationState;

use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\WeekRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class WeekRangeTest extends TestCase
{
    public function testWeekRangeCreate(): void
    {
        $datetimeImmutable = new DateTimeImmutable('2021-05-01');

        $weekRange = WeekRange::createFromDate($datetimeImmutable);

        $this->assertEquals('2021-04-26 00:00:00', $weekRange->getDateWeekStart()->format('Y-m-d H:i:s'));
        $this->assertEquals('2021-05-02 23:59:59', $weekRange->getDateWeekEnd()->format('Y-m-d H:i:s'));
    }
}
