<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use ArrayAccess;
use Iterator;

class CommissionList implements Iterator, ArrayAccess
{
    /**
     * @var Commission[]
     */
    private array $commissions;

    public function __construct()
    {
        $this->commissions = [];
    }

    /**
     * @param Commission $commission
     *
     * @return $this
     */
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


    public function rewind(): void
    {
        reset($this->commissions);
    }

    public function current(): Commission
    {
        return current($this->commissions);
    }

    public function key(): string
    {
        return key($this->commissions);
    }

    public function next(): void
    {
        next($this->commissions);
    }

    public function valid(): bool
    {
        return key($this->commissions) !== null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->commissions[] = $value;
        } else {
            $this->commissions[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->commissions[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->commissions[$offset]);
    }

    public function offsetGet($offset): ?Commission
    {
        return isset($this->commissions[$offset]) ? $this->commissions[$offset] : null;
    }
}
