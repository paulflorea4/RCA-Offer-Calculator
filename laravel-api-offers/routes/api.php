<?php

use App\Http\Controllers\RcaApiController;
use Illuminate\Support\Facades\Route;

//ofer
Route::get('/offer/{id}',[RcaApiController::class,'download']);
Route::get('/policy',[RcaApiController::class,'policy']);
Route::post('/policy',[RcaApiController::class,'transformOfferIntoPolicy']);
Route::get('/policy/{id}',[RcaApiController::class,'downloadById']);

//nomenclature
Route::get('/nomenclature/country',[RcaApiController::class,'countries']);
Route::get('/nomenclature/county',[RcaApiController::class,'counties']);
Route::get('/nomenclature/locality/{county_code}',[RcaApiController::class,'localities']);

//vehicle
Route::get('/vehicle',[RcaApiController::class,'vehicle']);

//company
Route::get('/company/{taxId}',[RcaApiController::class,'company']);

//product
Route::get('/product',[RcaApiController::class,'product']);

