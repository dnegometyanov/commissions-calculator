<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\Rules;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Commissions\CalculatorContext\Domain\Entity\ExchangeRates;
use Commissions\CalculatorContext\Domain\Entity\Transaction;
use Commissions\CalculatorContext\Domain\Service\CommissionsCalculator\CalculationState\UserCalculationStateCollection;
use Commissions\CalculatorContext\Domain\ValueObject\TransactionType;
use Exception;

class BusinessWithdrawRule implements RuleInterface
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
        return $transaction->getTransactionType()->isWithdraw()
            && $transaction->getUser()->getUserType()->isBusiness();
    }

    /** @inheritDoc */
    public function calculate(
        Transaction $transaction,
        UserCalculationStateCollection $userCalculationStateCollection,
        ExchangeRates $exchangeRates = null
    ): RuleResult {
        $userWithdrawCalculationState = $userCalculationStateCollection->getByTransactionType(TransactionType::withdraw());

        if ($userWithdrawCalculationState->isTransactionBeforeWeekRange($transaction)) {
            throw new Exception(
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
