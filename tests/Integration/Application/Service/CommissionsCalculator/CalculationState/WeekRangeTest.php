<?php declare(strict_types=1);

namespace CommissionsTest\Integration\Application\Service\CommissionsCalculator\CalculationState;

use Brick\Money\Money;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState\UserCalculationStateRepositoryDefault;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CalculationState\WeekRange;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\CommissionCalculator;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\CommonDepositRule;
use Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules\RulesSequence;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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