<?php

use App\Http\Controllers\AntrolBpjsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatetimePasienController;
use App\Http\Controllers\DetilTindakanController;
use App\Http\Controllers\KlaimController;
use App\Http\Controllers\ReferensiMjknController;
use App\Http\Controllers\SinkronSepController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('testting');
// });

// 🟢 route login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/', [KlaimController::class, 'index'])->middleware('auth.session')->name('index');
Route::prefix('monitoring')->name('monitoring.')->middleware('auth.session')->group(function () {

    Route::prefix('sinkron-sep')->name('sinkron_sep.')->group(function () {
        Route::get('/', [SinkronSepController::class, 'index'])->name('index');
    });
    Route::prefix('antrol')->name('antrol.')->group(function () {
        Route::get('/', [AntrolBpjsController::class, 'index'])->name('index');
        Route::get('/manual/taksid', [AntrolBpjsController::class, 'form_manual_send_taksid'])->name('form_manual_send_taksid');
        Route::post('/kirim/taskid', [AntrolBpjsController::class, 'send_taskid'])->name('send_taskid');
        Route::post('/kirim/manual/taskid', [AntrolBpjsController::class, 'manual_send_taskid'])->name('manual_send_taskid');
    });
    Route::prefix('referensi-mjkn')->name('referensi_mjkn.')->group(function () {
        Route::get('/', [ReferensiMjknController::class, 'index'])->name('index');
        Route::delete('/', [ReferensiMjknController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('mutasi-berkas')->name('mutasi_berkas.')->group(function () {
        Route::get('/', [DatetimePasienController::class, 'index'])->name('index');
        Route::post('/', [DatetimePasienController::class, 'repair'])->name('repair');
    });

    Route::prefix('klaim')->name('klaim.')->group(function () {
        Route::get('/', [KlaimController::class, 'index'])->name('index');
        Route::get('/mode-copy', [KlaimController::class, 'mode_copy'])->name('mode_copy');
        Route::post('/klaim/export', [KlaimController::class, 'exportExcel'])->name('export');

    });

});

Route::prefix('jaspel')->name('jaspel.')->middleware('auth.session')->group(function () {

    Route::prefix('detil')->name('detil.')->group(function () {
        Route::get('/', [DetilTindakanController::class, 'index'])->name('index');
        Route::post('/xportp', [DetilTindakanController::class, 'export'])->name('export');
    });

});
Route::get('/detil-tindakan/data', [DetilTindakanController::class, 'loadData'])
    ->name('detil-tindakan.data');

Route::post('/logout', function () {
    session()->forget('is_logged_in');
    return redirect()->route('login');
})->name('logout');

// Route::get('/',[AutoBillingController::class, 'index'])->name('autobilling.index');
// Route::post('/',[AutoBillingController::class, 'store_all'])->name('nota_jalan.store_all');
