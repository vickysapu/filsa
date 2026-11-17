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

            // FINANCE MODULE - KEUANGAN GEREJA FILADELFIA MDF IV JABAR
            // === TRANSAKSI ===
            ['name' => 'finance.transaction.view', 'display_name' => 'Lihat Transaksi', 'group' => 'Finance.Transaksi'],
            ['name' => 'finance.transaction.create', 'display_name' => 'Tambah Transaksi', 'group' => 'Finance.Transaksi'],
            ['name' => 'finance.transaction.edit', 'display_name' => 'Edit Transaksi', 'group' => 'Finance.Transaksi'],
            ['name' => 'finance.transaction.delete', 'display_name' => 'Hapus Transaksi', 'group' => 'Finance.Transaksi'],
            ['name' => 'finance.transaction.approve', 'display_name' => 'Setujui Transaksi', 'group' => 'Finance.Transaksi'],

            // FINANCE LAPORAN
            ['name' => 'finance.report.view', 'display_name' => 'Lihat Laporan', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.create', 'display_name' => 'Buat Laporan', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.edit', 'display_name' => 'Edit Laporan', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.delete', 'display_name' => 'Hapus Laporan', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.submit', 'display_name' => 'Kirim Laporan ke Pusat', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.forward', 'display_name' => 'Teruskan Laporan ke Atasan', 'group' => 'Finance.Laporan'], // ✅ BARU
            ['name' => 'finance.report.approve', 'display_name' => 'Setujui Laporan', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.consolidate', 'display_name' => 'Konsolidasi Laporan', 'group' => 'Finance.Laporan'],
            ['name' => 'finance.report.export', 'display_name' => 'Export Laporan Excel', 'group' => 'Finance.Laporan'],

            // CHURCH STRUCTURE
            ['name' => 'church_structure.view', 'display_name' => 'Lihat Struktur Gereja', 'group' => 'Church Structure'],
            ['name' => 'church_structure.create', 'display_name' => 'Tambah Posisi', 'group' => 'Church Structure'],
            ['name' => 'church_structure.edit', 'display_name' => 'Edit Posisi', 'group' => 'Church Structure'],
            ['name' => 'church_structure.delete', 'display_name' => 'Hapus Posisi', 'group' => 'Church Structure'],

            // CHURCH PROFILE
            ['name' => 'church_profile.view', 'display_name' => 'Lihat Profil Gereja', 'group' => 'Church Profile'],
            ['name' => 'church_profile.create', 'display_name' => 'Tambah Profil Gereja', 'group' => 'Church Profile'],
            ['name' => 'church_profile.edit', 'display_name' => 'Edit Profil Gereja', 'group' => 'Church Profile'],
            ['name' => 'church_profile.delete', 'display_name' => 'Hapus Profil Gereja', 'group' => 'Church Profile'],

            // === AKSES PUSAT CABANG & PUSAT ===
            ['name' => 'finance.center.view', 'display_name' => 'Lihat Laporan Diterima (Pusat Cabang)', 'group' => 'Finance.Admin'],
            ['name' => 'finance.center.approve', 'display_name' => 'Setujui/Tolak Laporan Cabang (Pusat Cabang)', 'group' => 'Finance.Admin'],
            ['name' => 'finance.manage.all', 'display_name' => 'Akses Penuh Keuangan (Super Admin)', 'group' => 'Finance.Admin'],

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
            
            // Inventaris
            ['name' => 'inventaris.view', 'display_name' => 'Lihat Inventaris', 'group' => 'Inventaris'],
            ['name' => 'inventaris.create', 'display_name' => 'Buat Inventaris', 'group' => 'Inventaris'],
            ['name' => 'inventaris.edit', 'display_name' => 'Edit Inventaris', 'group' => 'Inventaris'],
            ['name' => 'inventaris.delete', 'display_name' => 'Hapus Inventaris', 'group' => 'Inventaris'],
            
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

            //API Request Data
            ['name' => 'data.access.view', 'display_name' => 'View Data Requests', 'group' => 'data-access'],
            ['name' => 'data.access.manage', 'display_name' => 'Manage Data Access', 'group' => 'data-access'],
            ['name' => 'data.access.logs', 'display_name' => 'View Access Logs', 'group' => 'data-access'],

            // DATA TABLES
            ['name' => 'data-tables.view', 'display_name' => 'Lihat Data Tables', 'group' => 'data-tables'],
            ['name' => 'data-tables.create', 'display_name' => 'Tambah Data Tables', 'group' => 'data-tables'],
            ['name' => 'data-tables.edit', 'display_name' => 'Edit Data Tables', 'group' => 'data-tables'],
            ['name' => 'data-tables.delete', 'display_name' => 'Hapus Data Tables', 'group' => 'data-tables'],
            ['name' => 'data-tables.toggleStatus', 'display_name' => 'Status Data Tables', 'group' => 'data-tables'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
