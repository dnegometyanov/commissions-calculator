<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class UserType
{
    const USER_TYPE_PRIVATE  = 'private';
    const USER_TYPE_BUSINESS = 'business';

    private string $clientType;

    private function __construct(
        string $clientType
    )
    {
        $this->clientType = $clientType;
    }

    public static function create(string $clientType): UserType
    {
        if (!in_array($clientType, self::getClientTypes())) {
            throw new Exception(sprintf('Client type %s is not available', $clientType));
        }

        return new self($clientType);
    }

    public function getValue(): string
    {
        return $this->clientType;
    }

    public function is(string $clientType): bool
    {
        return $this->clientType === $clientType;
    }

    public static function getClientTypes(): array
    {
        return [
            self::USER_TYPE_PRIVATE,
            self::USER_TYPE_BUSINESS,
        ];
    }
}