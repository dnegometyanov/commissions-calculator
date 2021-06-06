<?php

declare(strict_types=1);

namespace CommissionsTest\Unit\Domain\Entity;

use Commissions\CalculatorContext\Domain\Entity\User;
use Commissions\CalculatorContext\Domain\ValueObject\UserType;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function testCanCreateUser(): void
    {
        $user = User::create(1, UserType::of('private'));

        $this->assertEquals(1, $user->getId());
        $this->assertTrue($user->getUserType()->is(UserType::of('private')));
    }
}
