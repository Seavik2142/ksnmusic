<?php

namespace Tests\Unit\KoelPlus\Models;

use App\Enums\Acl\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\PlusTestCase;

use function Tests\create_admin;
use function Tests\create_manager;

class UserTest extends PlusTestCase
{
    #[Test]
    public function adminCanAssignAllRolesOrderedByLevel(): void
    {
        $admin = create_admin();

        self::assertSame([Role::GUEST, Role::USER, Role::ARTIST, Role::MANAGER, Role::ADMIN], $admin->getAssignableRoles()->all());
    }

    #[Test]
    public function managerCannotAssignAdminRole(): void
    {
        $manager = create_manager();

        self::assertSame([Role::GUEST, Role::USER, Role::ARTIST, Role::MANAGER], $manager->getAssignableRoles()->all());
    }
}
