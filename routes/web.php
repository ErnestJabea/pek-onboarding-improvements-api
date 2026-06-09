<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PdfDownloadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('language.switch');

Route::get('/', function () {
    return response()->json(['status' => 'online', 'service' => 'PEK FCP API']);
});

// Téléchargement des PDFs d'onboarding (protégé par l'authentification Filament)
Route::middleware(['web', 'auth'])
    ->prefix('admin/pdf')
    ->name('admin.pdf.')
    ->group(function () {
        Route::get('/onboarding/{session}/{type}', [PdfDownloadController::class, 'download'])
            ->name('download')
            ->where('type', 'kyc|risk|labft');
    });

