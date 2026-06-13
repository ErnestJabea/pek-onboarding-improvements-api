<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/run-migrations', function() {
    Artisan::call('migrate', ['--force' => true]);
    return Artisan::output();
});

Route::get('/clear-cache', function() {
    Artisan::call('optimize:clear');
    return Artisan::output() ?: "Cache en ligne vide avec succes !";
});

Route::get('/diagnose-queue', function() {
    $results = [];
    try {
        $results['db_connection'] = config('database.default');
        $results['queue_connection'] = config('queue.default');
        
        // Count pending jobs
        if (\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
            $results['pending_jobs_count'] = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $results['pending_jobs_sample'] = \Illuminate\Support\Facades\DB::table('jobs')->limit(5)->get()->toArray();
        } else {
            $results['pending_jobs_table'] = "Table 'jobs' does not exist.";
        }
        
        // Count failed jobs
        if (\Illuminate\Support\Facades\Schema::hasTable('failed_jobs')) {
            $results['failed_jobs_count'] = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            $results['failed_jobs_sample'] = \Illuminate\Support\Facades\DB::table('failed_jobs')->orderBy('id', 'desc')->limit(5)->get()->toArray();
        } else {
            $results['failed_jobs_table'] = "Table 'failed_jobs' does not exist.";
        }
        
        // Test mail sending synchronously (without queue)
        if (request()->has('test_email')) {
            $testEmail = request()->query('test_email');
            \Illuminate\Support\Facades\Mail::raw("Ceci est un email de test pour diagnostiquer la configuration SMTP de l'API PEK.", function($message) use ($testEmail) {
                $message->to($testEmail)->subject("Test SMTP PEK");
            });
            $results['smtp_test'] = "Email de test envoye avec succes a {$testEmail} (en direct, sans queue).";
        } else {
            $results['smtp_test'] = "Ajoutez ?test_email=votre_email@example.com a l'URL pour tester l'envoi SMTP en direct.";
        }
    } catch (\Exception $e) {
        $results['error'] = $e->getMessage();
        $results['trace'] = $e->getTraceAsString();
    }
    
    return response()->json($results);
});

Route::get('/run-queue', function() {
    $results = [];
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
            return response()->json(['message' => 'La table jobs n\'existe pas.']);
        }
        
        $initialCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
        $results['initial_jobs_count'] = $initialCount;
        
        if ($initialCount === 0) {
            return response()->json(['message' => 'La file d\'attente est vide.', 'jobs_processed' => 0]);
        }
        
        $processed = 0;
        $limit = 40; // Limite pour éviter les timeouts HTTP
        
        while ($processed < $limit && \Illuminate\Support\Facades\DB::table('jobs')->count() > 0) {
            Artisan::call('queue:work', [
                'connection' => 'database',
                '--once' => true,
            ]);
            $processed++;
        }
        
        $results['message'] = "Traitement effectue.";
        $results['jobs_processed'] = $processed;
        $results['remaining_jobs_count'] = \Illuminate\Support\Facades\DB::table('jobs')->count();
    } catch (\Exception $e) {
        $results['error'] = $e->getMessage();
    }
    return response()->json($results);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/products', [ProductController::class, 'index']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/bank-details', function() {
    return response()->json(\App\Models\BankDetail::where('is_active', true)->first());
});

// Protected routes
Route::post('/stripe/webhook', [WebhookController::class, 'handleStripe']);
Route::post('/coolpay/webhook', [WebhookController::class, 'handleCoolPay']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/dashboard-stats', [AuthController::class, 'dashboardStats']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/update-password', [AuthController::class, 'updatePassword']);
    Route::get('/notifications', function (Request $request) {
        return $request->user()->notifications()->orderBy('created_at', 'desc')->get();
    });
    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    });
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::post('/subscriptions/{id}/check-status', [SubscriptionController::class, 'checkCoolPayStatus']);

    // Valorisation en temps réel du portefeuille FCP (positions détaillées)
    Route::get('/portfolio/valuation', [AuthController::class, 'portfolioValuation']);

    // Onboarding Client FCP
    Route::get('/onboarding/status', [\App\Http\Controllers\OnboardingController::class, 'status']);
    Route::post('/onboarding/save-progress', [\App\Http\Controllers\OnboardingController::class, 'saveProgress']);
    Route::post('/onboarding/finalize', [\App\Http\Controllers\OnboardingController::class, 'finalize']);
});
