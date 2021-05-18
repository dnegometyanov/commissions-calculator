<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Application\Service\CommissionsCalculator\Rules;

use Brick\Money\Money;
use Commissions\CalculatorContext\Domain\Entity\Transaction;

interface RuleInterface
{
    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function isSuitable(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return Money
     */
    public function calculateCommissionAmount(Transaction $transaction): Money;
}
