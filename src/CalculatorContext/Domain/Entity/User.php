<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\Entity;

use Commissions\CalculatorContext\Domain\ValueObject\UserType;

class User
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var UserType
     */
    private UserType $userType;

    private function __construct(
        int $id,
        UserType $userType
    )
    {
        $this->id       = $id;
        $this->userType = $userType;
    }

    public static function create(int $id, UserType $userType): User
    {
        return new self($id, $userType);
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return UserType
     */
    public function getUserType(): UserType
    {
        return $this->userType;
    }
}
