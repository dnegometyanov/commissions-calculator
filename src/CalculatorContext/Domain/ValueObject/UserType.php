<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

class UserType
{
    /**
     * @var string
     */
    private string $userType;

    /**
     * @param string $userType
     */
    private function __construct(string $userType)
    {
        $this->userType = $userType;
    }

    /**
     * @param string $userType
     *
     * @return UserType
     */
    public static function of(string $userType): UserType
    {
        return new self($userType);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->userType;
    }

    /**
     * @param UserType $userType
     *
     * @return bool
     */
    public function is(UserType $userType): bool
    {
        return $this->userType === $userType->getValue();
    }
}
