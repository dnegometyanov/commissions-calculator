<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class UserType
{
    private const USER_TYPE_PRIVATE  = 'private';
    private const USER_TYPE_BUSINESS = 'business';

    private const USER_TYPES = [
        self::USER_TYPE_PRIVATE,
        self::USER_TYPE_BUSINESS,
    ];

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
     * @return UserType
     */
    public static function private(): UserType
    {
        return new self(self::USER_TYPE_PRIVATE);
    }

    /**
     * @return UserType
     */
    public static function business(): UserType
    {
        return new self(self::USER_TYPE_BUSINESS);
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->userType === self::USER_TYPE_PRIVATE;
    }

    /**
     * @return bool
     */
    public function isBusiness(): bool
    {
        return $this->userType === self::USER_TYPE_BUSINESS;
    }

    /**
     * @param string $clientType
     *
     * @return UserType
     *
     * @throws Exception
     */
    public static function createFromValue(string $clientType): UserType
    {
        if (!in_array($clientType, self::USER_TYPES, true)) {
            throw new Exception(sprintf('Client type %s is not available', $clientType));
        }

        return new self($clientType);
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
