<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailParseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('parse_email', [EmailParseController::class,'index']);
// Route::get('pdf-to-text', [EmailParseController::class,'pdfToText']);
Route::get('pdf-to-text', [EmailParseController::class,'pdfToExcelAPi']);
Route::get('pdf-to-text-another', [EmailParseController::class,'pdfToExcelAnother']);
Route::get('pdf-to-json', [EmailParseController::class,'pdfToJson']);

Route::get('/', function () {
    return view('welcome');
});
