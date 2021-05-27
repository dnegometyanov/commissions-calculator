<?php

declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class UserType
{
    public const USER_TYPE_PRIVATE  = 'private';
    public const USER_TYPE_BUSINESS = 'business';

    /**
     * @var string
     */
    private string $clientType;

    /**
     * @param string $clientType
     */
    private function __construct(string $clientType)
    {
        $this->clientType = $clientType;
    }

    /**
     * @param string $clientType
     *
     * @return UserType
     *
     * @throws Exception
     */
    public static function create(string $clientType): UserType
    {
        if (!in_array($clientType, self::getClientTypes(), true)) {
            throw new Exception(sprintf('Client type %s is not available', $clientType));
        }

        return new self($clientType);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->clientType;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->clientType === self::USER_TYPE_PRIVATE;
    }

    /**
     * @return bool
     */
    public function isBusiness(): bool
    {
        return $this->clientType === self::USER_TYPE_BUSINESS;
    }

    /**
     * @return array|string[]
     */
    public static function getClientTypes(): array
    {
        return [
            self::USER_TYPE_PRIVATE,
            self::USER_TYPE_BUSINESS,
        ];
    }
}
