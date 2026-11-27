<?php

use App\Http\Controllers\Api\DataAccessController;
use App\Http\Controllers\DokumentasiController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Public\DataAccessPortalController;
use App\Http\Controllers\Public\DataAccessRequestController;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicChurchApiController;

Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('public.about');
Route::get('/events', [HomeController::class, 'events'])->name('public.events');
Route::get('/event/{slug}', [HomeController::class, 'eventDetail'])->name('public.event.detail');
Route::get('/galeri', [HomeController::class, 'gallery'])->name('public.gallery');
Route::get('/galeri/{slug}', [HomeController::class, 'galleryAlbum'])->name('public.gallery.album');
Route::get('/kontak', [HomeController::class, 'contact'])->name('public.contact');
Route::prefix('api/public')->name('api.public.')->middleware(['throttle:api_public'])->group(function () {
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

Route::prefix('v1/data')->name('api.data.')->middleware(['throttle:api-data'])->group(function () {
    Route::get('/{table}', [DataAccessController::class, 'index'])->name('index');
    Route::get('/{table}/{id}', [DataAccessController::class, 'show'])->name('show');
    Route::get('/files/{table}/{id}/{column}', [DataAccessController::class, 'file'])->name('file')->middleware(['signed', 'throttle:120,1']);
});

Route::get('/request-data-access', [DataAccessRequestController::class, 'showForm'])->name('public.data-access.request');
Route::post('/request-data-access', [DataAccessRequestController::class, 'store'])->name('public.data-access.store');
Route::get('/request-data-access/confirm/{id}/{key}', [DataAccessRequestController::class, 'confirm'])->where('id', '[0-9a-fA-F\-]{36}')->middleware('throttle:10,1')->name('public.data-access.confirm');

Route::prefix('data-portal')->name('data-portal.')->middleware(['throttle:60,1'])->group(function () {
    Route::get('/', [DataAccessPortalController::class, 'showVerification'])->name('verify');
    Route::post('/verify', [DataAccessPortalController::class, 'verifyToken'])->name('verify.post');
    Route::middleware(['verify.portal.token'])->group(function () {
        Route::get('/dashboard', [DataAccessPortalController::class, 'showDashboard'])->name('dashboard');
        Route::get('/data/{table}', [DataAccessPortalController::class, 'fetchTableData'])->name('data.fetch');
        Route::get('/data/{table}/export/csv', [DataAccessPortalController::class, 'exportCsv'])->name('data.export.csv');
    });
    Route::post('/token', [DataAccessPortalController::class, 'addToken'])
        ->name('token.add')
        ->middleware('throttle:10,1');
    Route::post('/logout', [DataAccessPortalController::class, 'logout'])->name('logout');
});

Route::get('/api/public/church/jemaat', [PublicChurchApiController::class, 'jemaat'])->middleware('throttle:api_public');
Route::get('/api/public/church/structures', [PublicChurchApiController::class, 'structures'])->middleware('throttle:api_public');