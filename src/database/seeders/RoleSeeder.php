<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $pemilik = Role::firstOrCreate(['name' => 'pemilik_apotek']);
        $petugas = Role::firstOrCreate(['name' => 'petugas_apotek']);

        // Daftar permission dari resources & pages
        $permissions = [
            // Obat
            'view_any_obat', 'view_obat', 'create_obat', 'update_obat', 'delete_obat', 'delete_any_obat',
            // Supplier
            'view_any_supplier', 'view_supplier', 'create_supplier', 'update_supplier', 'delete_supplier', 'delete_any_supplier',
            // Kategori
            'view_any_kategori::obat', 'view_kategori::obat', 'create_kategori::obat', 'update_kategori::obat', 'delete_kategori::obat', 'delete_any_kategori::obat',
            // Obat Masuk
            'view_any_obat::masuk', 'view_obat::masuk', 'create_obat::masuk', 'update_obat::masuk', 'delete_obat::masuk', 'delete_any_obat::masuk',
            // Penjualan
            'view_any_penjualan', 'view_penjualan', 'create_penjualan', 'update_penjualan', 'delete_penjualan', 'delete_any_penjualan',
            // Users
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'delete_any_user',
            // Activity log
            'view_any_activity', 'view_activity', 'create_activity', 'update_activity', 'delete_activity', 'delete_any_activity',
            // Roles
            'view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role', 'delete_any_role',
            // Pages & Widgets
            'page_LaporanPage',
            'widget_OverlookWidget',
            'widget_StokHampirHabisWidget',
            'widget_HampirKedaluwarsaWidget',
            'widget_PenjualanChartWidget',
            'widget_LatestAccessLogs',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Assign ke Pemilik Apotek
        $pemilikPermissions = [
            'view_any_obat', 'view_obat', 'create_obat', 'update_obat', 'delete_obat',
            'view_any_supplier', 'view_supplier', 'create_supplier', 'update_supplier', 'delete_supplier',
            'view_any_kategori::obat', 'view_kategori::obat', 'create_kategori::obat', 'update_kategori::obat', 'delete_kategori::obat',
            'view_any_obat::masuk', 'view_obat::masuk', 'create_obat::masuk',
            'view_any_penjualan', 'view_penjualan', 'create_penjualan',
            'page_LaporanPage',
            'widget_StokHampirHabisWidget',
            'widget_HampirKedaluwarsaWidget',
            'widget_PenjualanChartWidget',
        ];
        $pemilik->syncPermissions($pemilikPermissions);

        // Assign ke Petugas Apotek
        $petugasPermissions = [
            'view_any_obat', 'view_obat',
            'view_any_supplier', 'view_supplier',
            'view_any_kategori::obat', 'view_kategori::obat',
            'view_any_obat::masuk', 'view_obat::masuk', 'create_obat::masuk',
            'view_any_penjualan', 'view_penjualan', 'create_penjualan',
            'widget_StokHampirHabisWidget',
            'widget_HampirKedaluwarsaWidget',
        ];
        $petugas->syncPermissions($petugasPermissions);
    }
}
