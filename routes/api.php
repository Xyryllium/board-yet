<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::options('{any}', function () {
    return response('', 200);
})->where('any', '.*');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyFromEmail'])
    ->name('verification.verify');

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0')
    ]);
});

Route::post('/organizations/invitations/accept',
    [OrganizationMemberController::class, 'acceptInvitation']
);
Route::get('/organizations/invitations/details/{id}', 
    [OrganizationMemberController::class, 'listOrgDetails']
);


Route::middleware([AuthenticateApiToken::class])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'currentUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verify', [EmailVerificationController::class, 'verify']);
    Route::post('/email/resend', [EmailVerificationController::class, 'resend']);
    
    Route::get('/organizations/subdomain/validate', [OrganizationController::class, 'validateSubdomain']);
    Route::get('/organizations/subdomain/details/{subdomain}', [OrganizationController::class, 'listOrgDetailsBySubdomain']);
});

Route::middleware([AuthenticateApiToken::class, 'org.scope'])->group(function () {
    Route::get('/boards', [BoardController::class, 'index']);
    Route::get('/boards/{board}', [BoardController::class, 'show']);
    Route::get('/boards/{id}/columns', [ColumnController::class, 'index']);
    
    Route::get('/columns/{columnId}/cards', [CardController::class, 'index']);
    
    Route::get('/users/{organizationId}/members', [UserController::class, 'show']);
});

Route::middleware([AuthenticateApiToken::class, 'verified'])->group(function () {
    Route::post('/organizations', [OrganizationController::class, 'store']);
    
    Route::middleware(['org.scope'])->group(function () {
        Route::prefix('organizations')->group(function () {
            Route::post('/{id}/invite', [OrganizationMemberController::class, 'invite']);
            Route::put('/{id}/settings', [OrganizationController::class, 'updateSettings']);
        });
        
        Route::prefix('boards')->group(function () {
            Route::post('/', [BoardController::class, 'store']);
            Route::put('/{id}', [BoardController::class, 'update']);
        });
        
        Route::prefix('columns')->group(function () {
            Route::post('/', [ColumnController::class, 'store']);
            Route::put('/reorder', [ColumnController::class, 'reorder']);
            Route::put('/{id}', [ColumnController::class, 'update']);
            Route::delete('/{id}', [ColumnController::class, 'destroy']);
            Route::post('/{columnId}/cards', [CardController::class, 'store']);
        });
        
        Route::prefix('cards')->group(function () {
            Route::put('/{id}', [CardController::class, 'update']);
            Route::delete('/{id}', [CardController::class, 'destroy']);
        });
    });
});