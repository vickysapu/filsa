<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DataAccessApprovalController;
use App\Http\Controllers\Admin\DataTableController;
use App\Http\Controllers\Admin\MeetingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChurchProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\ChurchStructureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumentasiController;
use App\Http\Controllers\DokumentasiKategoriController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventKategoriController;
use App\Http\Controllers\Finance\CategoryMappingController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\FinancialExportController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\JemaatController;
use App\Http\Controllers\KategoriPelayananController;
use App\Http\Controllers\LatihanPelayananController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\PemasukanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecurityTokenController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/api/notifications', function () {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }
        $notifications = $user
            ->notifications()
            ->whereNull('read_at')
            ->limit(10)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->data['title'] ?? 'Notifikasi',
                    'message' => $notif->data['message'] ?? '',
                    'url' => $notif->data['url'] ?? '#',
                    'feature' => $notif->data['feature'] ?? 'UMUM',
                    'icon' => $notif->data['icon'] ?? 'ri-notification-3-line',
                    'color' => $notif->data['color'] ?? 'text-muted',
                    'created_at' => $notif->created_at->diffForHumans(),
                ];
            });
        return response()->json($notifications);
    });

    Route::post('/api/notifications/{id}/read', function ($id) {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    });

    Route::post('/api/notifications/read-all', function () {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    });

    Route::get('/api/notifications', function () {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        $notifications = $user
            ->unreadNotifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'feature' => strtoupper($notif->data['source'] ?? ($notif->data['feature'] ?? 'UMUM')),
                    'data' => [
                        'title' => $notif->data['title'] ?? 'Notifikasi',
                        'message' => $notif->data['message'] ?? '',
                        'url' => $notif->data['url'] ?? '#',
                        'icon' => $notif->data['icon'] ?? 'ri-notification-3-line',
                        'color' => $notif->data['color'] ?? 'text-muted',
                    ],
                    'created_at' => $notif->created_at->toIso8601String(),
                ];
            });

        return response()->json($notifications);
    });

    Route::post('/api/notifications/read-feature/{feature}', function ($feature) {
        $user = Auth::user();
        $feature = strtoupper($feature);

        $user->unreadNotifications
            ->filter(function ($notif) use ($feature) {
                $source = strtoupper($notif->data['source'] ?? ($notif->data['feature'] ?? 'UMUM'));
                return $source === $feature;
            })
            ->each->markAsRead();

        return response()->json(['success' => true]);
    });
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware(['auth', 'security.fingerprint']);

Route::prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

Route::get('/Filadelfia/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware(['auth', 'security.audit']);

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'security.audit', 'page.token'])
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

        Route::prefix('jemaat')
            ->name('jemaat.')
            ->middleware(['auth', 'permission:jemaat.view'])
            ->group(function () {
                Route::get('/api/list', [JemaatController::class, 'apiIndex'])->name('api.list');
                Route::get('/{jemaat}/api', [JemaatController::class, 'apiShow'])->name('api.show');
                Route::get('/', [JemaatController::class, 'index'])->name('index');
                Route::get('/export', [JemaatController::class, 'export'])->name('export');
                Route::middleware(['permission:jemaat.create'])->group(function () {
                    Route::get('/create', [JemaatController::class, 'create'])->name('create');
                    Route::post('', [JemaatController::class, 'store'])->name('store');
                });
                Route::middleware(['permission:jemaat.edit'])->group(function () {
                    Route::get('/{jemaat}/edit', [JemaatController::class, 'edit'])->name('edit');
                    Route::put('/{jemaat}', [JemaatController::class, 'update'])->name('update');
                });
                Route::middleware(['permission:jemaat.delete'])->group(function () {
                    Route::delete('/{jemaat}', [JemaatController::class, 'destroy'])->name('destroy');
                });
            });

        Route::prefix('church-structure')
            ->name('church_structure.')
            ->middleware(['auth', 'security.fingerprint', 'security.audit', 'page.token'])
            ->group(function () {
                Route::middleware(['permission:church_structure.view'])->group(function () {
                    Route::get('/', [ChurchStructureController::class, 'index'])->name('index');
                    Route::get('/{churchStructure}', [ChurchStructureController::class, 'show'])->name('show');
                    Route::get('/api/tree', [ChurchStructureController::class, 'apiTree'])->name('api.tree');
                });
                Route::middleware(['permission:church_structure.create'])->group(function () {
                    Route::get('/create', [ChurchStructureController::class, 'create'])->name('create');
                    Route::post('/', [ChurchStructureController::class, 'store'])->name('store');
                });
                Route::middleware(['permission:church_structure.edit'])->group(function () {
                    Route::get('/{churchStructure}/edit', [ChurchStructureController::class, 'edit'])->name('edit');
                    Route::put('/{churchStructure}', [ChurchStructureController::class, 'update'])->name('update');
                    Route::post('/reorder', [ChurchStructureController::class, 'reorder'])->name('reorder');
                });
                Route::middleware(['permission:church_structure.delete'])->group(function () {
                    Route::delete('/{churchStructure}', [ChurchStructureController::class, 'destroy'])->name('destroy');
                });
            });

        Route::prefix('church-profile')
            ->name('church_profile.')
            ->middleware(['auth', 'permission:church_profile.view'])
            ->group(function () {
                Route::get('/', [ChurchProfileController::class, 'index'])->name('index');
                Route::middleware(['permission:church_profile.create'])->group(function () {
                    Route::get('/create', [ChurchProfileController::class, 'create'])->name('create');
                    Route::post('/', [ChurchProfileController::class, 'store'])->name('store');
                });
                Route::middleware(['permission:church_profile.edit'])->group(function () {
                    Route::get('/{churchProfile}/edit', [ChurchProfileController::class, 'edit'])->name('edit');
                    Route::put('/{churchProfile}', [ChurchProfileController::class, 'update'])->name('update');
                    Route::post('/{churchProfile}/remove-gallery', [ChurchProfileController::class, 'removeGalleryImage'])->name('remove-gallery');
                    Route::post('/{churchProfile}/remove-document', [ChurchProfileController::class, 'removeDocument'])->name('remove-document');
                });
                Route::get('/{churchProfile}/preview', [ChurchProfileController::class, 'preview'])->name('preview');
                Route::post('/{churchProfile}/toggle-publish', [ChurchProfileController::class, 'togglePublish'])->name('toggle-publish');
                Route::middleware(['permission:church_profile.delete'])->group(function () {
                    Route::delete('/{churchProfile}', [ChurchProfileController::class, 'destroy'])->name('destroy');
                });
            });

        Route::prefix('finance')
            ->name('finance.')
            ->middleware(['page.token'])
            ->group(function () {
                Route::prefix('transactions')
                    ->name('transactions.')
                    ->group(function () {
                        Route::middleware('permission:finance.transaction.view')->group(function () {
                            Route::get('/', [FinancialTransactionController::class, 'index'])->name('index');
                            Route::get('/create', [FinancialTransactionController::class, 'create'])->name('create');
                            Route::get('/show/{transaction}', [FinancialTransactionController::class, 'show'])->name('show');
                            Route::get('/{transaction}/edit', [FinancialTransactionController::class, 'edit'])->name('edit');
                        });
                        Route::middleware('permission:finance.transaction.create')->group(function () {
                            Route::post('/store', [FinancialTransactionController::class, 'store'])->name('store');
                        });
                        Route::middleware('permission:finance.transaction.edit')->group(function () {
                            Route::put('/{transaction}', [FinancialTransactionController::class, 'update'])->name('update');
                        });
                        Route::middleware('permission:finance.transaction.delete')->group(function () {
                            Route::delete('/{transaction}', [FinancialTransactionController::class, 'destroy'])->name('destroy');
                        });
                        Route::middleware('permission:finance.transaction.approve')->group(function () {
                            Route::post('/{transaction}/approve', [FinancialTransactionController::class, 'approve'])->name('approve');
                        });
                    });

                Route::middleware('permission:finance.report.view')->group(function () {
                    Route::resource('churches', ChurchController::class)->except(['create', 'show', 'edit']);
                });

                Route::middleware('permission:finance.report.edit')->group(function () {
                    Route::post('churches/{church}/link', [ChurchController::class, 'link'])->name('churches.link');
                    Route::post('churches/{church}/migrate', [ChurchController::class, 'migrate'])->name('churches.migrate');
                });

                Route::prefix('reports')
                    ->name('reports.')
                    ->group(function () {
                        // 1. Route Spesifik (Tanpa Parameter) - WAJIB DI ATAS
                        Route::middleware('permission:finance.report.consolidate')->group(function () {
                            Route::post('/consolidate', [FinancialReportController::class, 'consolidateReports'])->name('consolidate');
                            Route::post('/pusat-download', [FinancialReportController::class, 'pusatDownload'])->name('pusatDownload');
                        });

                        Route::middleware('permission:finance.report.edit')->group(function () {
                            Route::put('/{report}', [FinancialReportController::class, 'update'])->name('update');
                            Route::get('/category-mapping', [CategoryMappingController::class, 'index'])->name('category-mapping');
                            Route::post('/category-mapping/update', [CategoryMappingController::class, 'update'])->name('category-mapping.update');
                        });

                        Route::middleware('permission:finance.report.create')->group(function () {
                            Route::post('/store-report', [FinancialReportController::class, 'storeReport'])->name('storeReport');
                            Route::post('/generate', [FinancialReportController::class, 'generateFromTransactions'])->name('generate');
                            Route::get('/import-template', [FinancialReportController::class, 'downloadTemplate'])->name('import.template');
                            Route::post('/import', [FinancialReportController::class, 'importExcel'])->name('import');
                            Route::post('/import/analyze', [FinancialReportController::class, 'importAnalyze'])->name('import.analyze');
                            Route::post('/import/columns', [FinancialReportController::class, 'importColumns'])->name('import.columns');
                            Route::post('/import/preview', [FinancialReportController::class, 'importPreview'])->name('import.preview');
                            Route::post('/import/commit', [FinancialReportController::class, 'importCommit'])->name('import.commit');
                        });

                        Route::middleware('permission:finance.report.submit')->group(function () {
                            Route::post('/submit-folder', [FinancialReportController::class, 'submitFolder'])->name('submit-folder'); // Dipindah ke atas
                        });

                        // 2. Route dengan Parameter Wildcard `/{report}` - WAJIB DI BAWAH
                        Route::middleware('permission:finance.report.view')->group(function () {
                            Route::get('/', [FinancialReportController::class, 'index'])->name('index');
                            Route::get('/create', [FinancialReportController::class, 'create'])->name('create');
                            Route::get('/received', [FinancialReportController::class, 'receivedReports'])->name('received');
                            Route::get('/{report}', [FinancialReportController::class, 'show'])->name('show');
                            Route::get('/{report}/edit', [FinancialReportController::class, 'edit'])->name('edit');
                        });

                        Route::middleware('permission:finance.report.delete')->group(function () {
                            Route::delete('/{report}', [FinancialReportController::class, 'destroy'])->name('destroy');
                        });

                        Route::middleware('permission:finance.report.submit')->group(function () {
                            Route::post('/{report}/submit', [FinancialReportController::class, 'submit'])->name('submit');
                        });

                        Route::middleware('permission:finance.report.approve')->group(function () {
                            Route::post('/{report}/approve', [FinancialReportController::class, 'approve'])->name('approve');
                            Route::post('/{report}/reject', [FinancialReportController::class, 'reject'])->name('reject');
                            Route::post('/approve-folder', [FinancialReportController::class, 'approveFolder'])->name('approve-folder');
                            Route::post('/reject-folder', [FinancialReportController::class, 'rejectFolder'])->name('reject-folder');
                        });

                        Route::middleware('permission:finance.report.forward')->group(function () {
                            Route::post('/{report}/forward', [FinancialReportController::class, 'forward'])->name('forward');
                        });
                    });

                Route::prefix('export')
                    ->name('export.')
                    ->group(function () {
                        Route::middleware('permission:finance.report.export')->group(function () {
                            Route::get('/consolidated', [FinancialExportController::class, 'exportFormLapkeuMD'])->name('consolidated');
                            Route::get('/monthly-income', [FinancialExportController::class, 'exportDaftarPemasukanJabar'])->name('monthly-income');
                            Route::get('/transactions', [FinancialExportController::class, 'exportLaporanKeuanganMDFJabar'])->name('transactions');
                            Route::get('/annual-report', [FinancialExportController::class, 'exportFormLapkeuMD'])->name('annual-report');
                            Route::get('/transactions-pdf', [FinancialExportController::class, 'exportLaporanKeuanganPDF'])->name('transactions-pdf');
                            Route::get('/run', [FinancialExportController::class, 'run'])->name('run');
                            Route::get('/templates', [FinancialExportController::class, 'templateIndex'])->name('templates.index');
                            Route::post('/templates', [FinancialExportController::class, 'templateStore'])->name('templates.store');
                            Route::put('/templates/{id}', [FinancialExportController::class, 'templateUpdate'])->name('templates.update');
                            Route::delete('/templates/{id}', [FinancialExportController::class, 'templateDestroy'])->name('templates.destroy');
                        });
                    });
            });

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

        Route::prefix('latihan-pelayanan')
            ->name('latihan-pelayanan.')
            ->group(function () {
                Route::resource('/', LatihanPelayananController::class)
                    ->parameters(['' => 'latihan'])
                    ->except(['destroy']);
                Route::get('/{latihan}/peserta', [LatihanPelayananController::class, 'peserta'])->name('peserta.index');
                Route::post('/{latihan}/peserta', [LatihanPelayananController::class, 'storePeserta'])->name('peserta.store');
                Route::delete('/{latihan}', [LatihanPelayananController::class, 'destroy'])->name('destroy');
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
