<?php

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role as RoleModel;

return new class extends Migration {
    public function up(): void
    {
        $role = RoleModel::findOrCreate(Role::ARTIST->value);
        $role->givePermissionTo([
            Permission::MANAGE_SONGS,
        ]);
    }

    public function down(): void
    {
        RoleModel::findByName(Role::ARTIST->value)?->delete();
    }
};
