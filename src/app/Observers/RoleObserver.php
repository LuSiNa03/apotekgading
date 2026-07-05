<?php

namespace App\Observers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleObserver
{
    /**
     * Setiap kali role disimpan (termasuk saat Shield sync permissions),
     * flush cache Spatie agar perubahan hak akses langsung efektif
     * tanpa perlu restart server atau clear cache manual.
     */
    public function saved(Role $role): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function deleted(Role $role): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
