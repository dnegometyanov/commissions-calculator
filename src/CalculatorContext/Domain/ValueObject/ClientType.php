<?php declare(strict_types=1);

namespace Commissions\CalculatorContext\Domain\ValueObject;

use Exception;

class ClientType
{
    const CLIENT_TYPE_PRIVATE  = 'private';
    const CLIENT_TYPE_BUSINESS = 'business';

    private string $clientType;

    private function __construct(
        string $clientType
    )
    {
        $this->clientType = $clientType;
    }

    public static function create(string $clientType): ClientType
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
            self::CLIENT_TYPE_PRIVATE,
            self::CLIENT_TYPE_BUSINESS,
        ];
    }
}