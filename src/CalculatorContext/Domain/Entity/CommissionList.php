<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

class CommissionList
{
    /**
     * @var Commission[]
     */
    private array $commissions;

    public function __construct()
    {
        $this->commissions = [];
    }

    public function addCommission(Commission $commission): CommissionList
    {
        $this->commissions[(string)$commission->getTransaction()->getUuid()] = $commission;

        return $this;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Commission
     */
    public function findCommission(Transaction $transaction): Commission
    {
        return $this->commissions[(string)$transaction->getUuid()];
    }

    /**
     * @return Commission[]
     */
    public function toArray(): array
    {
        return $this->commissions;
    }
}
