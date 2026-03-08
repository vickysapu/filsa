<?php

use App\Http\Controllers\JemaatController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumentasiController;
use App\Http\Controllers\DokumentasiKategoriController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventKategoriController;
use App\Http\Controllers\KategoriPelayananController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\PemasukanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('public.about');
Route::get('/events', [HomeController::class, 'events'])->name('public.events');
Route::get('/event/{slug}', [HomeController::class, 'eventDetail'])->name('public.event.detail');
Route::get('/galeri', [HomeController::class, 'gallery'])->name('public.gallery');
Route::get('/galeri/{slug}', [HomeController::class, 'galleryAlbum'])->name('public.gallery.album');
Route::get('/kontak', [HomeController::class, 'contact'])->name('public.contact');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('Filadelfia')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    
    // Users Management
    Route::middleware(['permission:users.view'])->get('/users', [UserController::class, 'index'])->name('users.index');
    Route::middleware(['permission:users.create'])->get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::middleware(['permission:users.create'])->post('/users', [UserController::class, 'store'])->name('users.store');
    Route::middleware(['permission:users.edit'])->get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::middleware(['permission:users.edit'])->put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::middleware(['permission:users.delete'])->delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::middleware(['permission:users.view'])->get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    
    // Roles Management
    Route::middleware(['permission:roles.view'])->get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::middleware(['permission:roles.create'])->get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::middleware(['permission:roles.create'])->post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware(['permission:roles.edit'])->get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::middleware(['permission:roles.edit'])->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::middleware(['permission:roles.delete'])->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::middleware(['permission:roles.view'])->get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    
    // Permissions Management
    Route::middleware(['permission:permissions.view'])->get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::middleware(['permission:permissions.create'])->get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::middleware(['permission:permissions.create'])->post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::middleware(['permission:permissions.delete'])->delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    //jemaat
    Route::middleware(['permission:jemaat.view'])->get('/jemaat', [JemaatController::class, 'index'])->name('jemaat.index');
    Route::middleware(['permission:jemaat.create'])->get('/jemaat/create', [JemaatController::class, 'create'])->name('jemaat.create');
    Route::middleware(['permission:jemaat.create'])->post('/jemaat', [JemaatController::class, 'store'])->name('jemaat.store');
    Route::middleware(['permission:jemaat.view'])->get('/jemaat/{jemaat}', [JemaatController::class, 'show'])->name('jemaat.show');
    Route::middleware(['permission:jemaat.edit'])->get('/jemaat/{jemaat}/edit', [JemaatController::class, 'edit'])->name('jemaat.edit');
    Route::middleware(['permission:jemaat.edit'])->put('/jemaat/{jemaat}', [JemaatController::class, 'update'])->name('jemaat.update');
    Route::middleware(['permission:jemaat.delete'])->delete('/jemaat/{jemaat}', [JemaatController::class, 'destroy'])->name('jemaat.destroy');
    Route::middleware(['permission:jemaat.view'])->get('/jemaat/export', [JemaatController::class, 'export'])->name('jemaat.export');

     
    // Pemasukan Routes
    Route::middleware(['permission:pemasukan.view'])->get('/pemasukan', [PemasukanController::class, 'index'])->name('pemasukan.index');
    Route::middleware(['permission:pemasukan.create'])->get('/pemasukan/create', [PemasukanController::class, 'create'])->name('pemasukan.create');
    Route::middleware(['permission:pemasukan.create'])->post('/pemasukan', [PemasukanController::class, 'store'])->name('pemasukan.store');
    Route::middleware(['permission:pemasukan.view'])->get('/pemasukan/{pemasukan}', [PemasukanController::class, 'show'])->name('pemasukan.show');
    Route::middleware(['permission:pemasukan.edit'])->get('/pemasukan/{pemasukan}/edit', [PemasukanController::class, 'edit'])->name('pemasukan.edit');
    Route::middleware(['permission:pemasukan.edit'])->put('/pemasukan/{pemasukan}', [PemasukanController::class, 'update'])->name('pemasukan.update');
    Route::middleware(['permission:pemasukan.verify'])->post('/pemasukan/{pemasukan}/verify', [PemasukanController::class, 'verify'])->name('pemasukan.verify');
    Route::middleware(['permission:pemasukan.verify'])->post('/pemasukan/{pemasukan}/reject', [PemasukanController::class, 'reject'])->name('pemasukan.reject');
    Route::middleware(['permission:pemasukan.delete'])->delete('/pemasukan/{pemasukan}', [PemasukanController::class, 'destroy'])->name('pemasukan.destroy');
    Route::middleware(['permission:pemasukan.view'])->get('/pemasukan/export', [PemasukanController::class, 'export'])->name('pemasukan.export');
    Route::middleware(['permission:pemasukan.view'])->get('/pemasukan/statistics', [PemasukanController::class, 'statistics'])->name('pemasukan.statistics');

    // Pengeluaran Routes
    Route::middleware(['permission:pengeluaran.view'])->get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
    Route::middleware(['permission:pengeluaran.create'])->get('/pengeluaran/create', [PengeluaranController::class, 'create'])->name('pengeluaran.create');
    Route::middleware(['permission:pengeluaran.create'])->post('/pengeluaran', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
    Route::middleware(['permission:pengeluaran.view'])->get('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'show'])->name('pengeluaran.show');
    Route::middleware(['permission:pengeluaran.edit'])->get('/pengeluaran/{pengeluaran}/edit', [PengeluaranController::class, 'edit'])->name('pengeluaran.edit');
    Route::middleware(['permission:pengeluaran.edit'])->put('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'update'])->name('pengeluaran.update');
    Route::middleware(['permission:pengeluaran.verify'])->post('/pengeluaran/{pengeluaran}/verify', [PengeluaranController::class, 'verify'])->name('pengeluaran.verify');
    Route::middleware(['permission:pengeluaran.verify'])->post('/pengeluaran/{pengeluaran}/reject', [PengeluaranController::class, 'reject'])->name('pengeluaran.reject');
    Route::middleware(['permission:pengeluaran.delete'])->delete('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');
    Route::middleware(['permission:pengeluaran.view'])->get('/pengeluaran/export', [PengeluaranController::class, 'export'])->name('pengeluaran.export');
    Route::middleware(['permission:pengeluaran.view'])->get('/pengeluaran/statistics', [PengeluaranController::class, 'statistics'])->name('pengeluaran.statistics');

    // Dashboard Keuangan
    Route::middleware(['permission:laporan.dashboard'])->get('/keuangan/dashboard', [KeuanganController::class, 'dashboard'])->name('keuangan.dashboard');
    
    // Laporan Keuangan
    Route::middleware(['permission:laporan.view'])->get('/keuangan/laporan', [KeuanganController::class, 'laporan'])->name('keuangan.laporan');
    Route::middleware(['permission:laporan.export'])->get('/keuangan/laporan/export', [KeuanganController::class, 'exportLaporan'])->name('keuangan.laporan.export');
    
    // Chart API
    Route::middleware(['permission:laporan.view'])->get('/keuangan/chart-data', [KeuanganController::class, 'chartData'])->name('keuangan.chart');

    Route::prefix('pelayanan/kategori')->name('kategori.pelayanan.')->group(function () {
        Route::middleware(['permission:pelayanan.kategori.view'])->get('/', [KategoriPelayananController::class, 'index'])->name('index');
        Route::middleware(['permission:pelayanan.kategori.create'])->post('/', [KategoriPelayananController::class, 'store'])->name('store');
        Route::middleware(['permission:pelayanan.kategori.edit'])->put('/{kategori}', [KategoriPelayananController::class, 'update'])->name('update');
        Route::middleware(['permission:pelayanan.kategori.delete'])->delete('/{kategori}', [KategoriPelayananController::class, 'destroy'])->name('destroy');
        Route::get('/api', [KategoriPelayananController::class, 'apiKategoris'])->name('api');
    });
    
    // --- Jadwal Pelayanan Routes ---
    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::middleware(['permission:pelayanan.view'])->get('/', [PelayananController::class, 'index'])->name('index');
        Route::middleware(['permission:pelayanan.create'])->get('/create', [PelayananController::class, 'create'])->name('create');
        Route::middleware(['permission:pelayanan.create'])->post('/', [PelayananController::class, 'store'])->name('store');
        Route::middleware(['permission:pelayanan.view'])->get('/{pelayanan}', [PelayananController::class, 'show'])->name('show');
        Route::middleware(['permission:pelayanan.edit'])->get('/{pelayanan}/edit', [PelayananController::class, 'edit'])->name('edit');
        Route::middleware(['permission:pelayanan.edit'])->put('/{pelayanan}', [PelayananController::class, 'update'])->name('update');
        Route::middleware(['permission:pelayanan.delete'])->delete('/{pelayanan}', [PelayananController::class, 'destroy'])->name('destroy');
    });
    
    // --- Data Pelayan (Petugas)
    Route::prefix('pelayan')->name('pelayan.')->group(function () {
        Route::get('/', function() {
            return view('admin.pelayanan.pelayan.index');
        })->name('index')->middleware(['permission:pelayanan.view']);
    });
    
    // API
    Route::get('/api/search-jemaat', [PelayananController::class, 'searchJemaat'])->name('api.search-jemaat');

    // --- Event Kategori Routes ---
    Route::prefix('event-kategori')->name('event-kategori.')->group(function () {
        Route::middleware(['permission:event.kategori.view'])->get('/', [EventKategoriController::class, 'index'])->name('index');
        Route::middleware(['permission:event.kategori.create'])->post('/', [EventKategoriController::class, 'store'])->name('store');
        Route::middleware(['permission:event.kategori.edit'])->put('/{kategori}', [EventKategoriController::class, 'update'])->name('update');
        Route::middleware(['permission:event.kategori.delete'])->delete('/{kategori}', [EventKategoriController::class, 'destroy'])->name('destroy');
    });

    // --- Event Routes ---
    Route::prefix('events')->name('events.')->group(function () {
        Route::middleware(['permission:event.view'])->get('/', [EventController::class, 'index'])->name('index');
        Route::middleware(['permission:event.create'])->get('/create', [EventController::class, 'create'])->name('create');
        Route::middleware(['permission:event.create'])->post('/', [EventController::class, 'store'])->name('store');
        Route::middleware(['permission:event.view'])->get('/{event}', [EventController::class, 'show'])->name('show');
        Route::middleware(['permission:event.edit'])->get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::middleware(['permission:event.edit'])->put('/{event}', [EventController::class, 'update'])->name('update');
        Route::middleware(['permission:event.delete'])->delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
    });

    // --- Dokumentasi Kategori Routes ---
    Route::prefix('dokumentasi-kategori')->name('dokumentasi-kategori.')->group(function () {
        Route::middleware(['permission:dokumentasi.kategori.view'])->get('/', [DokumentasiKategoriController::class, 'index'])->name('index');
        Route::middleware(['permission:dokumentasi.kategori.create'])->post('/', [DokumentasiKategoriController::class, 'store'])->name('store');
        Route::middleware(['permission:dokumentasi.kategori.edit'])->put('/{kategori}', [DokumentasiKategoriController::class, 'update'])->name('update');
        Route::middleware(['permission:dokumentasi.kategori.delete'])->delete('/{kategori}', [DokumentasiKategoriController::class, 'destroy'])->name('destroy');
    });

    // --- Dokumentasi Album Routes ---
    Route::prefix('dokumentasi')->name('dokumentasi.')->group(function () {
        Route::middleware(['permission:dokumentasi.view'])->get('/', [DokumentasiController::class, 'index'])->name('index');
        Route::middleware(['permission:dokumentasi.create'])->get('/create', [DokumentasiController::class, 'create'])->name('create');
        Route::middleware(['permission:dokumentasi.create'])->post('/', [DokumentasiController::class, 'store'])->name('store');
        Route::middleware(['permission:dokumentasi.view'])->get('/{album}', [DokumentasiController::class, 'show'])->name('show');
        Route::middleware(['permission:dokumentasi.edit'])->get('/{album}/edit', [DokumentasiController::class, 'edit'])->name('edit');
        Route::middleware(['permission:dokumentasi.edit'])->put('/{album}', [DokumentasiController::class, 'update'])->name('update');
        Route::middleware(['permission:dokumentasi.delete'])->delete('/{album}', [DokumentasiController::class, 'destroy'])->name('destroy');
        Route::middleware(['permission:dokumentasi.edit'])->post('/{album}/upload', [DokumentasiController::class, 'uploadPhotos'])->name('upload');
    });
});

Route::prefix('api/public')->name('api.public.')->group(function () {
    
    // Events API
    Route::get('/events/upcoming', [EventController::class, 'apiUpcoming'])->name('events.upcoming');
    Route::get('/events/featured', [EventController::class, 'apiFeatured'])->name('events.featured');
    Route::get('/events/{slug}', [EventController::class, 'apiShow'])->name('events.show');
    
    // Dokumentasi API
    Route::get('/dokumentasi/featured', [DokumentasiController::class, 'apiFeatured'])->name('dokumentasi.featured');
    Route::get('/dokumentasi/kategori/{slug}', [DokumentasiController::class, 'apiByCategory'])->name('dokumentasi.category');
    Route::get('/dokumentasi/{slug}', [DokumentasiController::class, 'apiShow'])->name('dokumentasi.show');
    
});