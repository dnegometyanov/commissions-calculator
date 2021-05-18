<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

class User
{
    private int $id;

    private function __construct(
        int $id
    )
    {
        $this->id = $id;
    }

    public static function create(int $id): User
    {
        return new self($id);
    }

    public function getId(): int
    {
        return $this->id;
    }
}