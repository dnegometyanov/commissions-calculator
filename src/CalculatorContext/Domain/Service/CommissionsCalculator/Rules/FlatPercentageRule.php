<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\Exception\TransactionsNotSortedException;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules\RuleCondition\ConditionInterface;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;

class FlatPercentageRule implements RuleInterface
{
    /**
     * @var Currency
     */
    private Currency $baseCurrency;

    /**
     * @var TransactionType
     */
    private TransactionType $stateSelectorByTransactionType;

    /**
     * @var string
     */
    private string $commonPercentage;

    /**
     * @var ConditionInterface
     */
    private ConditionInterface $condition;

    /**
     * @param ConditionInterface $condition
     * @param TransactionType $stateSelectorByTransactionType
     * @param Currency $baseCurrency
     * @param string $commonPercentage
     */
    public function __construct(
        ConditionInterface $condition,
        TransactionType $stateSelectorByTransactionType, // to select proper UserCalculationState for the Transaction's type
        Currency $baseCurrency,
        string $commonPercentage
    ) {
        $this->baseCurrency     = $baseCurrency;
        $this->commonPercentage = $commonPercentage;
        $this->condition        = $condition;
        $this->stateSelectorByTransactionType = $stateSelectorByTransactionType;
    }

    /** @inheritDoc */
    public function isSuitable(Transaction $transaction): bool
    {
        return $this->condition->isSuitable($transaction);
    }

    /** @inheritDoc */
    public function calculate(
        Transaction $transaction,
        UserCalculationStateCollection $userCalculationStateCollection,
        ExchangeRates $exchangeRates = null
    ): RuleResult {
        $userWithdrawCalculationState = $userCalculationStateCollection->getByTransactionType($this->stateSelectorByTransactionType);

        if ($userWithdrawCalculationState->isTransactionBeforeWeekRange($transaction)) {
            throw new TransactionsNotSortedException(
                sprintf(
                    'Transactions should be sorted in ascending order by date, error for transaction with id %s and date %s',
                    (string)$transaction->getUuid(),
                    $transaction->getDateTime()->format('Y-m-d H:i:s')
                )
            );
        }

        $commissionAmount = $transaction->getAmount()->multipliedBy(
            $this->commonPercentage,
            RoundingMode::HALF_UP
        );

        return new RuleResult(
            $userWithdrawCalculationState,
            $commissionAmount
        );
    }
}
