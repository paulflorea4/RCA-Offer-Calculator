<?php

use App\Http\Controllers\RcaApiController;

Route::post('/rca/offer', [RcaApiController::class, 'showOffers'])->name('rca.offer');
Route::get('/', [RcaApiController::class, 'showRcaForm'])->name('rca.form');
Route::get('/rca/offer/download/{offerId}',
    [RcaApiController::class, 'download']
)->name('rca.offer.download');
Route::post('/rca/policy', [RcaApiController::class, 'issuePolicy'])
    ->name('rca.policy');
Route::get('/rca/policy/download/{id}', [RcaApiController::class, 'downloadById'])
    ->name('rca.policy.download');
