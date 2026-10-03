<?php

namespace Tests\Unit\Models;

use App\Enums\Acl\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\create_admin;
use function Tests\create_manager;
use function Tests\create_user;

class UserTest extends TestCase
{
    #[Test]
    public function adminCanAssignAvailableRolesOrderedByLevel(): void
    {
        $admin = create_admin();

        self::assertSame([Role::USER, Role::ARTIST, Role::ADMIN], $admin->getAssignableRoles()->all());
    }

    #[Test]
    public function managerCannotAssignRolesAboveTheirLevel(): void
    {
        $manager = create_manager();

        self::assertSame([Role::USER, Role::ARTIST], $manager->getAssignableRoles()->all());
    }

    #[Test]
    public function userWithoutManageAbilityCannotAssignAnyRoles(): void
    {
        $user = create_user();

        self::assertSame([], $user->getAssignableRoles()->all());
    }
}
