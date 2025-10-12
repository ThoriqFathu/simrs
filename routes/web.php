<?php

use App\Http\Controllers\AntrolBpjsController;
use App\Http\Controllers\DetilTindakanController;
use App\Http\Controllers\KlaimController;
use App\Http\Controllers\ReferensiMjknController;
use App\Http\Controllers\SinkronSepController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('testting');
// });

// Route::get('/', [AntrolBpjsController::class, 'index'])->name('index');
Route::prefix('monitoring')->name('monitoring.')->group(function () {

    Route::prefix('sinkron-sep')->name('sinkron_sep.')->group(function () {
        Route::get('/', [SinkronSepController::class, 'index'])->name('index');
    });
    Route::prefix('antrol')->name('antrol.')->group(function () {
        Route::get('/', [AntrolBpjsController::class, 'index'])->name('index');
        Route::post('/kirim/taskid', [AntrolBpjsController::class, 'send_taskid'])->name('send_taskid');
    });
    Route::prefix('referensi-mjkn')->name('referensi_mjkn.')->group(function () {
        Route::get('/', [ReferensiMjknController::class, 'index'])->name('index');
        Route::delete('/', [ReferensiMjknController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('klaim')->name('klaim.')->group(function () {
        Route::get('/', [KlaimController::class, 'index'])->name('index');
        Route::get('/mode-copy', [KlaimController::class, 'mode_copy'])->name('mode_copy');
        Route::post('/klaim/export', [KlaimController::class, 'exportExcel'])->name('export');

    });

});

Route::prefix('jaspel')->name('jaspel.')->group(function () {

    Route::prefix('detil')->name('detil.')->group(function () {
        Route::get('/', [DetilTindakanController::class, 'index'])->name('index');
        Route::post('/kirim/taskid', [DetilTindakanController::class, 'send_taskid'])->name('send_taskid');
    });

});
// Route::get('/',[AutoBillingController::class, 'index'])->name('autobilling.index');
// Route::post('/',[AutoBillingController::class, 'store_all'])->name('nota_jalan.store_all');
