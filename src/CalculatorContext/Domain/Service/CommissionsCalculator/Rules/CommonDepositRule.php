<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationState;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Exception;

class CommonDepositRule implements RuleInterface
{
    /**
     * @var Currency
     */
    private Currency $baseCurrency;

    /**
     * @var string
     */
    private string $commonPercentage;

    /**
     * @param Currency $baseCurrency
     * @param string $commonPercentage
     */
    public function __construct(
        Currency $baseCurrency,
        string $commonPercentage
    ) {
        $this->baseCurrency = $baseCurrency;
        $this->commonPercentage = $commonPercentage;
    }

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $transaction->getTransactionType()->isDeposit();
    }

    /** @inheritDoc */
    public function calculate(
        Transaction $transaction,
        UserCalculationStateCollection $userCalculationStateCollection,
        ExchangeRates $exchangeRates = null
    ): RuleResult {
        $userDepositCalculationState = $userCalculationStateCollection->getByTransactionType(TransactionType::deposit());

        if ($userDepositCalculationState->isTransactionBeforeWeekRange($transaction)) {
            throw new Exception(
                sprintf(
                    'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                    (string)$transaction->getUuid(),
                    $transaction->getDateTime()->format('Y-m-d H:i:s')
                )
            );
        }

        return new RuleResult(
            new UserCalculationState(), // TODO create new modified state
            $transaction->getAmount()->multipliedBy($this->commonPercentage, RoundingMode::HALF_UP)
        );
    }
}
