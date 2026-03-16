<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // USERS
            ['name' => 'users.view', 'display_name' => 'Lihat User', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Buat User', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit User', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Hapus User', 'group' => 'users'],

            // ROLES
            ['name' => 'roles.view', 'display_name' => 'Lihat Role', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Buat Role', 'group' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Role', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Hapus Role', 'group' => 'roles'],

            // PERMISSIONS
            ['name' => 'permissions.view', 'display_name' => 'Lihat Permission', 'group' => 'permissions'],
            ['name' => 'permissions.create', 'display_name' => 'Buat Permission', 'group' => 'permissions'],
            ['name' => 'permissions.delete', 'display_name' => 'Hapus Permission', 'group' => 'permissions'],

            // JEMAAT
            ['name' => 'jemaat.view', 'display_name' => 'Lihat Jemaat', 'group' => 'Jemaat'],
            ['name' => 'jemaat.create', 'display_name' => 'Buat Jemaat', 'group' => 'Jemaat'],
            ['name' => 'jemaat.edit', 'display_name' => 'Edit Jemaat', 'group' => 'Jemaat'],
            ['name' => 'jemaat.delete', 'display_name' => 'Hapus Jemaat', 'group' => 'Jemaat'],

            // PEMASUKAN
            ['name' => 'pemasukan.view', 'display_name' => 'Lihat Pemasukan', 'group' => 'Pemasukan'],
            ['name' => 'pemasukan.create', 'display_name' => 'Buat Pemasukan', 'group' => 'Pemasukan'],
            ['name' => 'pemasukan.delete', 'display_name' => 'Hapus Pemasukan', 'group' => 'Pemasukan'],
            ['name' => 'pemasukan.verify', 'display_name' => 'Verifikasi Pemasukan', 'group' => 'Pemasukan'],
            ['name' => 'pemasukan.export', 'display_name' => 'Export Pemasukan', 'group' => 'Pemasukan'],

            // PENGELUARAN
            ['name' => 'pengeluaran.view', 'display_name' => 'Lihat Pengeluaran', 'group' => 'Pengeluaran'],
            ['name' => 'pengeluaran.create', 'display_name' => 'Buat Pengeluaran', 'group' => 'Pengeluaran'],
            ['name' => 'pengeluaran.edit', 'display_name' => 'Edit Pengeluaran', 'group' => 'Pengeluaran'],
            ['name' => 'pengeluaran.verify', 'display_name' => 'Verifikasi Pengeluaran', 'group' => 'Pengeluaran'],
            ['name' => 'pengeluaran.delete', 'display_name' => 'Hapus Pengeluaran', 'group' => 'Pengeluaran'],

            // LAPORAN
            ['name' => 'laporan.view', 'display_name' => 'Lihat Laporan', 'group' => 'Laporan'],
            ['name' => 'laporan.export', 'display_name' => 'Export Laporan', 'group' => 'Laporan'],
            ['name' => 'laporan.dashboard', 'display_name' => 'Dashboard Laporan', 'group' => 'Laporan'],

            // PELAYANAN KATEGORI
            ['name' => 'pelayanan.kategori.view', 'display_name' => 'Lihat Kategori Pelayanan', 'group' => 'Pelayanan'],
            ['name' => 'pelayanan.kategori.create', 'display_name' => 'Tambah Kategori Pelayanan', 'group' => 'Pelayanan'],
            ['name' => 'pelayanan.kategori.edit', 'display_name' => 'Edit Kategori Pelayanan', 'group' => 'Pelayanan'],
            ['name' => 'pelayanan.kategori.delete', 'display_name' => 'Hapus Kategori Pelayanan', 'group' => 'Pelayanan'],

            // PELAYANAN
            ['name' => 'pelayanan.view', 'display_name' => 'Lihat Pelayanan', 'group' => 'Pelayanan'],
            ['name' => 'pelayanan.create', 'display_name' => 'Buat Pelayanan', 'group' => 'Pelayanan'],
            ['name' => 'pelayanan.edit', 'display_name' => 'Edit Pelayanan', 'group' => 'Pelayanan'],
            ['name' => 'pelayanan.delete', 'display_name' => 'Hapus Pelayanan', 'group' => 'Pelayanan'],

            // EVENT
            ['name' => 'event.view', 'display_name' => 'Lihat Event', 'group' => 'Event'],
            ['name' => 'event.create', 'display_name' => 'Buat Event', 'group' => 'Event'],
            ['name' => 'event.edit', 'display_name' => 'Edit Event', 'group' => 'Event'],
            ['name' => 'event.delete', 'display_name' => 'Hapus Event', 'group' => 'Event'],

            ['name' => 'event.kategori.view', 'display_name' => 'Lihat Kategori Event', 'group' => 'Event'],
            ['name' => 'event.kategori.create', 'display_name' => 'Tambah Kategori Event', 'group' => 'Event'],
            ['name' => 'event.kategori.edit', 'display_name' => 'Edit Kategori Event', 'group' => 'Event'],
            ['name' => 'event.kategori.delete', 'display_name' => 'Hapus Kategori Event', 'group' => 'Event'],

            // DOKUMENTASI
            ['name' => 'dokumentasi.view', 'display_name' => 'Lihat Dokumentasi', 'group' => 'Dokumentasi'],
            ['name' => 'dokumentasi.create', 'display_name' => 'Buat Dokumentasi', 'group' => 'Dokumentasi'],
            ['name' => 'dokumentasi.edit', 'display_name' => 'Edit Dokumentasi', 'group' => 'Dokumentasi'],
            ['name' => 'dokumentasi.delete', 'display_name' => 'Hapus Dokumentasi', 'group' => 'Dokumentasi'],

            ['name' => 'dokumentasi.kategori.view', 'display_name' => 'Lihat Kategori Dokumentasi', 'group' => 'Dokumentasi'],
            ['name' => 'dokumentasi.kategori.create', 'display_name' => 'Tambah Kategori Dokumentasi', 'group' => 'Dokumentasi'],
            ['name' => 'dokumentasi.kategori.edit', 'display_name' => 'Edit Kategori Dokumentasi', 'group' => 'Dokumentasi'],
            ['name' => 'dokumentasi.kategori.delete', 'display_name' => 'Hapus Kategori Dokumentasi', 'group' => 'Dokumentasi'],

            // BRANCH MANAGEMENT
            ['name' => 'branches.view', 'display_name' => 'Lihat Cabang', 'group' => 'branches'],
            ['name' => 'branches.create', 'display_name' => 'Tambah Cabang', 'group' => 'branches'],
            ['name' => 'branches.edit', 'display_name' => 'Edit Cabang', 'group' => 'branches'],
            ['name' => 'branches.delete', 'display_name' => 'Hapus Cabang', 'group' => 'branches'],

            // MEETINGS
            ['name' => 'meetings.view', 'display_name' => 'Lihat Meeting', 'group' => 'meetings'],
            ['name' => 'meetings.create', 'display_name' => 'Buat Meeting', 'group' => 'meetings'],
            ['name' => 'meetings.edit', 'display_name' => 'Edit Meeting', 'group' => 'meetings'],
            ['name' => 'meetings.delete', 'display_name' => 'Hapus Meeting', 'group' => 'meetings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
