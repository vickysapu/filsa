<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DataAccessApprovalController;
use App\Http\Controllers\Admin\DataTableController;
use App\Http\Controllers\Admin\MeetingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\DataAccessController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumentasiController;
use App\Http\Controllers\DokumentasiKategoriController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventKategoriController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\JemaatController;
use App\Http\Controllers\KategoriPelayananController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\LatihanPelayananController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\PemasukanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\Public\DataAccessPortalController;
use App\Http\Controllers\Public\DataAccessRequestController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\SecurityTokenController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('public.about');
Route::get('/events', [HomeController::class, 'events'])->name('public.events');
Route::get('/event/{slug}', [HomeController::class, 'eventDetail'])->name('public.event.detail');
Route::get('/galeri', [HomeController::class, 'gallery'])->name('public.gallery');
Route::get('/galeri/{slug}', [HomeController::class, 'galleryAlbum'])->name('public.gallery.album');
Route::get('/kontak', [HomeController::class, 'contact'])->name('public.contact');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware(['auth', 'security.fingerprint']);
Route::get('/Filadelfia/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware(['auth', 'security.fingerprint', 'security.audit']);

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'security.fingerprint', 'security.audit', 'page.token'])
    ->group(function () {
        Route::middleware(['permission:users.view'])->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        });
        Route::middleware(['permission:users.create'])
            ->get('/users/create', [UserController::class, 'create'])
            ->name('users.create');
        Route::middleware(['permission:users.edit'])
            ->get('/users/{user}/edit', [UserController::class, 'edit'])
            ->name('users.edit');

        Route::middleware(['permission:roles.view'])
            ->get('/roles', [RoleController::class, 'index'])
            ->name('roles.index');
        Route::middleware(['permission:roles.create'])
            ->get('/roles/create', [RoleController::class, 'create'])
            ->name('roles.create');
        Route::middleware(['permission:roles.edit'])
            ->get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->name('roles.edit');

        Route::middleware(['permission:permissions.view'])
            ->get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index');

        Route::middleware(['permission:branches.view'])
            ->prefix('branches')
            ->name('branches.')
            ->group(function () {
                Route::get('/', [BranchController::class, 'index'])->name('index');
            });

        Route::middleware(['permission:jemaat.view'])->group(function () {
            Route::get('/jemaat', [JemaatController::class, 'index'])->name('jemaat.index');
            Route::get('/jemaat/{jemaat}', [JemaatController::class, 'show'])->name('jemaat.show');
            Route::get('/jemaat/export', [JemaatController::class, 'export'])->name('jemaat.export');
        });
        Route::middleware(['permission:jemaat.create'])
            ->get('/jemaat/create', [JemaatController::class, 'create'])
            ->name('jemaat.create');
        Route::middleware(['permission:jemaat.edit'])
            ->get('/jemaat/{jemaat}/edit', [JemaatController::class, 'edit'])
            ->name('jemaat.edit');

        Route::middleware(['permission:pemasukan.view'])->group(function () {
            Route::get('/pemasukan', [PemasukanController::class, 'index'])->name('pemasukan.index');
            Route::get('/pemasukan/{pemasukan}', [PemasukanController::class, 'show'])->name('pemasukan.show');
            Route::get('/pemasukan/export', [PemasukanController::class, 'export'])->name('pemasukan.export');
            Route::get('/pemasukan/statistics', [PemasukanController::class, 'statistics'])->name('pemasukan.statistics');
        });
        Route::middleware(['permission:pemasukan.create'])
            ->get('/pemasukan/create', [PemasukanController::class, 'create'])
            ->name('pemasukan.create');
        Route::middleware(['permission:pemasukan.edit'])
            ->get('/pemasukan/{pemasukan}/edit', [PemasukanController::class, 'edit'])
            ->name('pemasukan.edit');

        Route::middleware(['permission:pengeluaran.view'])->group(function () {
            Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
            Route::get('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'show'])->name('pengeluaran.show');
            Route::get('/pengeluaran/export', [PengeluaranController::class, 'export'])->name('pengeluaran.export');
            Route::get('/pengeluaran/statistics', [PengeluaranController::class, 'statistics'])->name('pengeluaran.statistics');
        });
        Route::middleware(['permission:pengeluaran.create'])
            ->get('/pengeluaran/create', [PengeluaranController::class, 'create'])
            ->name('pengeluaran.create');
        Route::middleware(['permission:pengeluaran.edit'])
            ->get('/pengeluaran/{pengeluaran}/edit', [PengeluaranController::class, 'edit'])
            ->name('pengeluaran.edit');

        Route::middleware(['permission:laporan.dashboard'])
            ->get('/keuangan/dashboard', [KeuanganController::class, 'dashboard'])
            ->name('keuangan.dashboard');
        Route::middleware(['permission:laporan.view'])->group(function () {
            Route::get('/keuangan/laporan', [KeuanganController::class, 'laporan'])->name('keuangan.laporan');
            Route::get('/keuangan/chart-data', [KeuanganController::class, 'chartData'])->name('keuangan.chart');
        });
        Route::middleware(['permission:laporan.export'])
            ->get('/keuangan/laporan/export', [KeuanganController::class, 'exportLaporan'])
            ->name('keuangan.laporan.export');

        Route::middleware(['permission:pelayanan.kategori.view'])
            ->prefix('pelayanan/kategori')
            ->name('kategori.pelayanan.')
            ->group(function () {
                Route::get('/', [KategoriPelayananController::class, 'index'])->name('index');
            });
        Route::get('/pelayanan/kategori/api', [KategoriPelayananController::class, 'apiKategoris'])->name('kategori.pelayanan.api');

        Route::prefix('jadwal')
            ->name('jadwal.')
            ->group(function () {
                Route::middleware(['permission:pelayanan.view'])
                    ->get('/', [PelayananController::class, 'index'])
                    ->name('index');
                Route::middleware(['permission:pelayanan.create'])
                    ->get('/create', [PelayananController::class, 'create'])
                    ->name('create');
                Route::middleware(['permission:pelayanan.view'])
                    ->get('/{pelayanan}', [PelayananController::class, 'show'])
                    ->name('show');
                Route::middleware(['permission:pelayanan.edit'])
                    ->get('/{pelayanan}/edit', [PelayananController::class, 'edit'])
                    ->name('edit');
            });

        // === CRUD JADWAL LATIHAN PELAYANAN (Controller di luar folder Admin) ===
        Route::prefix('latihan-pelayanan')
            ->name('latihan-pelayanan.')
            ->group(function () {

                // Resource Route (index, create, store, show, edit, update)
                Route::resource('/', LatihanPelayananController::class)
                    ->parameters(['' => 'latihan'])
                    ->except(['destroy']);

                // Kelola Peserta
                Route::get('/{latihan}/peserta', [LatihanPelayananController::class, 'peserta'])
                    ->name('peserta.index');

                Route::post('/{latihan}/peserta', [LatihanPelayananController::class, 'storePeserta'])
                    ->name('peserta.store');

                Route::delete('/{latihan}', [LatihanPelayananController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('pelayan')
            ->name('pelayan.')
            ->group(function () {
                Route::middleware(['permission:pelayanan.view'])
                    ->get('/', fn() => view('admin.pelayanan.pelayan.index'))
                    ->name('index');
            });
        Route::get('/api/search-jemaat', [PelayananController::class, 'searchJemaat'])->name('api.search-jemaat');

        Route::middleware(['permission:event.kategori.view'])
            ->prefix('event-kategori')
            ->name('event-kategori.')
            ->group(function () {
                Route::get('/', [EventKategoriController::class, 'index'])->name('index');
            });

        Route::prefix('events')
            ->name('events.')
            ->group(function () {
                Route::middleware(['permission:event.view'])
                    ->get('/', [EventController::class, 'index'])
                    ->name('index');
                Route::middleware(['permission:event.create'])
                    ->get('/create', [EventController::class, 'create'])
                    ->name('create');
                Route::middleware(['permission:event.view'])
                    ->get('/{event}', [EventController::class, 'show'])
                    ->name('show');
                Route::middleware(['permission:event.edit'])
                    ->get('/{event}/edit', [EventController::class, 'edit'])
                    ->name('edit');
            });
            
            
        Route::middleware(['permission:inventaris.kategori.view'])
            ->prefix('inventaris-kategori')
            ->name('inventaris-kategori.')
            ->group(function () {
                Route::get('/', [InventarisController::class, 'index'])->name('index');
            });

        Route::prefix('inventaris')
            ->name('inventaris.')
            ->group(function () {
                Route::middleware(['permission:inventaris.view'])
                    ->get('/', [InventarisController::class, 'index'])
                    ->name('index');
                Route::middleware(['permission:inventaris.create'])
                    ->post('/', [InventarisController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:inventaris.view'])
                    ->get('/{inventaris}', [InventarisController::class, 'show'])
                    ->name('show');
                Route::middleware(['permission:inventaris.edit'])
                    ->get('/{inventaris}/edit', [InventarisController::class, 'edit'])
                    ->name('edit');
                Route::middleware(['permission:inventaris.edit'])
                    ->put('/{inventaris}', [InventarisController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:inventaris.delete'])
                    ->post('/delete/{id}', [InventarisController::class, 'destroy'])
                    ->name('delete');
            });

        Route::middleware(['permission:dokumentasi.kategori.view'])
            ->prefix('dokumentasi-kategori')
            ->name('dokumentasi-kategori.')
            ->group(function () {
                Route::get('/', [DokumentasiKategoriController::class, 'index'])->name('index');
            });

        Route::prefix('dokumentasi')
            ->name('dokumentasi.')
            ->group(function () {
                Route::middleware(['permission:dokumentasi.view'])
                    ->get('/', [DokumentasiController::class, 'index'])
                    ->name('index');
                Route::middleware(['permission:dokumentasi.create'])
                    ->get('/create', [DokumentasiController::class, 'create'])
                    ->name('create');
                Route::middleware(['permission:dokumentasi.view'])
                    ->get('/{album}', [DokumentasiController::class, 'show'])
                    ->name('show');
                Route::middleware(['permission:dokumentasi.edit'])
                    ->get('/{album}/edit', [DokumentasiController::class, 'edit'])
                    ->name('edit');
            });

        Route::prefix('security/tokens')
            ->name('security.tokens.')
            ->middleware(['permission:security.token.manage'])
            ->group(function () {
                Route::get('/', [SecurityTokenController::class, 'index'])->name('index');
            });

        Route::prefix('meetings')
            ->name('meetings.')
            ->group(function () {
                Route::middleware(['permission:meetings.view'])
                    ->get('/', [MeetingController::class, 'index'])
                    ->name('index');
                Route::middleware(['permission:meetings.create'])
                    ->get('/create', [MeetingController::class, 'create'])
                    ->name('create');
                Route::middleware(['permission:meetings.edit'])
                    ->get('/{meeting}/edit', [MeetingController::class, 'edit'])
                    ->name('edit');
                Route::middleware(['permission:meetings.view'])
                    ->get('/{meeting}', [MeetingController::class, 'show'])
                    ->name('show');
            });

        Route::prefix('data-access')
            ->name('data-access.')
            ->middleware(['ensure.administrator'])
            ->group(function () {
                Route::get('/requests', [DataAccessApprovalController::class, 'index'])->name('requests');
                Route::get('/requests/{dataRequest}', [DataAccessApprovalController::class, 'show'])->name('requests.show');
            });

        Route::prefix('data-tables')
            ->name('data-tables.')
            ->group(function () {
                Route::middleware(['permission:data-tables.view'])
                    ->get('/', [DataTableController::class, 'index'])
                    ->name('index');
                Route::middleware(['permission:data-tables.edit'])
                    ->get('/{dataTable}/edit', [DataTableController::class, 'edit'])
                    ->name('edit');
            });
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'security.fingerprint', 'security.audit'])
    ->group(function () {
        Route::middleware(['permission:users.create'])
            ->post('/users', [UserController::class, 'store'])
            ->name('users.store');
        Route::middleware(['permission:users.edit'])
            ->put('/users/{user}', [UserController::class, 'update'])
            ->name('users.update');
        Route::middleware(['permission:users.delete'])
            ->delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');

        Route::middleware(['permission:roles.create'])
            ->post('/roles', [RoleController::class, 'store'])
            ->name('roles.store');
        Route::middleware(['permission:roles.edit'])
            ->put('/roles/{role}', [RoleController::class, 'update'])
            ->name('roles.update');
        Route::middleware(['permission:roles.delete'])
            ->delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->name('roles.destroy');

        Route::middleware(['permission:permissions.create'])
            ->post('/permissions', [PermissionController::class, 'store'])
            ->name('permissions.store');
        Route::middleware(['permission:permissions.delete'])
            ->delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
            ->name('permissions.destroy');

        Route::prefix('branches')
            ->name('branches.')
            ->group(function () {
                Route::middleware(['permission:branches.create'])
                    ->post('/', [BranchController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:branches.edit'])
                    ->put('/{branch}', [BranchController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:branches.delete'])
                    ->delete('/{branch}', [BranchController::class, 'destroy'])
                    ->name('destroy');
                Route::middleware(['permission:branches.edit'])
                    ->post('/{branch}/toggle', [BranchController::class, 'toggleActive'])
                    ->name('toggle');
            });

        Route::middleware(['permission:jemaat.create'])
            ->post('/jemaat', [JemaatController::class, 'store'])
            ->name('jemaat.store');
        Route::middleware(['permission:jemaat.edit'])
            ->put('/jemaat/{jemaat}', [JemaatController::class, 'update'])
            ->name('jemaat.update');
        Route::middleware(['permission:jemaat.delete'])
            ->delete('/jemaat/{jemaat}', [JemaatController::class, 'destroy'])
            ->name('jemaat.destroy');

        Route::middleware(['permission:pemasukan.create'])
            ->post('/pemasukan', [PemasukanController::class, 'store'])
            ->name('pemasukan.store');
        Route::middleware(['permission:pemasukan.edit'])
            ->put('/pemasukan/{pemasukan}', [PemasukanController::class, 'update'])
            ->name('pemasukan.update');
        Route::middleware(['permission:pemasukan.verify'])->group(function () {
            Route::post('/pemasukan/{pemasukan}/verify', [PemasukanController::class, 'verify'])->name('pemasukan.verify');
            Route::post('/pemasukan/{pemasukan}/reject', [PemasukanController::class, 'reject'])->name('pemasukan.reject');
        });
        Route::middleware(['permission:pemasukan.delete'])
            ->delete('/pemasukan/{pemasukan}', [PemasukanController::class, 'destroy'])
            ->name('pemasukan.destroy');

        Route::middleware(['permission:pengeluaran.create'])
            ->post('/pengeluaran', [PengeluaranController::class, 'store'])
            ->name('pengeluaran.store');
        Route::middleware(['permission:pengeluaran.edit'])
            ->put('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'update'])
            ->name('pengeluaran.update');
        Route::middleware(['permission:pengeluaran.verify'])->group(function () {
            Route::post('/pengeluaran/{pengeluaran}/verify', [PengeluaranController::class, 'verify'])->name('pengeluaran.verify');
            Route::post('/pengeluaran/{pengeluaran}/reject', [PengeluaranController::class, 'reject'])->name('pengeluaran.reject');
        });
        Route::middleware(['permission:pengeluaran.delete'])
            ->delete('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'destroy'])
            ->name('pengeluaran.destroy');

        Route::prefix('pelayanan/kategori')
            ->name('kategori.pelayanan.')
            ->group(function () {
                Route::middleware(['permission:pelayanan.kategori.create'])
                    ->post('/', [KategoriPelayananController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:pelayanan.kategori.edit'])
                    ->put('/{kategori}', [KategoriPelayananController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:pelayanan.kategori.delete'])
                    ->delete('/{kategori}', [KategoriPelayananController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('jadwal')
            ->name('jadwal.')
            ->group(function () {
                Route::middleware(['permission:pelayanan.create'])
                    ->post('/', [PelayananController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:pelayanan.edit'])
                    ->put('/{pelayanan}', [PelayananController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:pelayanan.delete'])
                    ->delete('/{pelayanan}', [PelayananController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('event-kategori')
            ->name('event-kategori.')
            ->group(function () {
                Route::middleware(['permission:event.kategori.create'])
                    ->post('/', [EventKategoriController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:event.kategori.edit'])
                    ->put('/{kategori}', [EventKategoriController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:event.kategori.delete'])
                    ->delete('/{kategori}', [EventKategoriController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('events')
            ->name('events.')
            ->group(function () {
                Route::middleware(['permission:event.create'])
                    ->post('/', [EventController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:event.edit'])
                    ->put('/{event}', [EventController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:event.delete'])
                    ->delete('/{event}', [EventController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('dokumentasi-kategori')
            ->name('dokumentasi-kategori.')
            ->group(function () {
                Route::middleware(['permission:dokumentasi.kategori.create'])
                    ->post('/', [DokumentasiKategoriController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:dokumentasi.kategori.edit'])
                    ->put('/{kategori}', [DokumentasiKategoriController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:dokumentasi.kategori.delete'])
                    ->delete('/{kategori}', [DokumentasiKategoriController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('dokumentasi')
            ->name('dokumentasi.')
            ->group(function () {
                Route::middleware(['permission:dokumentasi.create'])
                    ->post('/', [DokumentasiController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:dokumentasi.edit'])
                    ->put('/{album}', [DokumentasiController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:dokumentasi.delete'])
                    ->delete('/{album}', [DokumentasiController::class, 'destroy'])
                    ->name('destroy');
                Route::middleware(['permission:dokumentasi.edit'])
                    ->post('/{album}/upload', [DokumentasiController::class, 'uploadPhotos'])
                    ->name('upload');
            });

        Route::prefix('security/tokens')
            ->name('security.tokens.')
            ->middleware(['permission:security.token.manage'])
            ->group(function () {
                Route::post('/', [SecurityTokenController::class, 'store'])->name('store');
                Route::put('/{token}/revoke', [SecurityTokenController::class, 'revoke'])->name('revoke');
                Route::put('/{token}/rotate', [SecurityTokenController::class, 'rotate'])->name('rotate');
                Route::post('/generate', [SecurityTokenController::class, 'generate'])->name('generate');
            });

        Route::prefix('meetings')
            ->name('meetings.')
            ->group(function () {
                Route::middleware(['permission:meetings.create'])
                    ->post('/', [MeetingController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:meetings.edit'])
                    ->put('/{meeting}', [MeetingController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:meetings.delete'])
                    ->delete('/{meeting}', [MeetingController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('data-access')
            ->name('data-access.')
            ->middleware(['ensure.administrator'])
            ->group(function () {
                Route::post('/requests/{dataRequest}/approve', [DataAccessApprovalController::class, 'approve'])->name('requests.approve');
                Route::post('/requests/{dataRequest}/reject', [DataAccessApprovalController::class, 'reject'])->name('requests.reject');
            });

        Route::prefix('data-tables')
            ->name('data-tables.')
            ->group(function () {
                Route::middleware(['permission:data-tables.create'])
                    ->post('/', [DataTableController::class, 'store'])
                    ->name('store');
                Route::middleware(['permission:data-tables.edit'])
                    ->put('/{dataTable}', [DataTableController::class, 'update'])
                    ->name('update');
                Route::middleware(['permission:data-tables.delete'])
                    ->delete('/{dataTable}', [DataTableController::class, 'destroy'])
                    ->name('destroy');
                Route::middleware(['permission:data-tables.toggleStatus'])
                    ->post('/{dataTable}/toggle', [DataTableController::class, 'toggleStatus'])
                    ->name('toggle');
            });
    });

Route::prefix('api/public')
    ->name('api.public.')
    ->middleware(['throttle:api_public'])
    ->group(function () {
        Route::get('/events/upcoming', [EventController::class, 'apiUpcoming'])->name('events.upcoming');
        Route::get('/events/featured', [EventController::class, 'apiFeatured'])->name('events.featured');
        Route::get('/events/{slug}', [EventController::class, 'apiShow'])->name('events.show');
        Route::get('/dokumentasi/featured', [DokumentasiController::class, 'apiFeatured'])->name('dokumentasi.featured');
        Route::get('/dokumentasi/kategori/{slug}', [DokumentasiController::class, 'apiByCategory'])->name('dokumentasi.category');
        Route::get('/dokumentasi/{slug}', [DokumentasiController::class, 'apiShow'])->name('dokumentasi.show');
    });

RateLimiter::for('login', fn($request) => Limit::perMinute(5)->by($request->ip() . '|' . $request->input('email')));
RateLimiter::for('api_public', fn($request) => Limit::perMinute(30)->by($request->ip()));

Route::get('/join-meeting/{meeting}', function ($meetingId) {
    $meeting = App\Models\Meeting::active()->where('id', $meetingId)->firstOrFail();
    return view('public.meetings.join', compact('meeting'));
})->name('public.meetings.join');

Route::prefix('v1/data')
    ->name('api.data.')
    ->middleware(['throttle:api-data'])
    ->group(function () {
        Route::get('/{table}', [DataAccessController::class, 'index'])->name('index');
        Route::get('/{table}/{id}', [DataAccessController::class, 'show'])->name('show');
    });

Route::get('/request-data-access', [DataAccessRequestController::class, 'showForm'])->name('public.data-access.request');
Route::post('/request-data-access', [DataAccessRequestController::class, 'store'])->name('public.data-access.store');

Route::prefix('data-portal')
    ->name('data-portal.')
    ->middleware(['throttle:60,1'])
    ->group(function () {
        Route::get('/', [DataAccessPortalController::class, 'showVerification'])->name('verify');
        Route::post('/verify', [DataAccessPortalController::class, 'verifyToken'])->name('verify.post');

        Route::middleware(['verify.portal.token'])->group(function () {
            Route::get('/dashboard', [DataAccessPortalController::class, 'showDashboard'])->name('dashboard');
            Route::get('/data/{table}', [DataAccessPortalController::class, 'fetchTableData'])->name('data.fetch');
            Route::get('/data/{table}/export/csv', [DataAccessPortalController::class, 'exportCsv'])->name('data.export.csv');
        });

        Route::post('/logout', [DataAccessPortalController::class, 'logout'])->name('logout');
    });
