<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::get('/organizations/subdomain/validate', [OrganizationController::class, 'validateSubdomain']);
    Route::get('/organizations/subdomain/details/{subdomain}', [OrganizationController::class, 'listOrgDetailsBySubdomain']);
});

Route::middleware([AuthenticateApiToken::class, 'org.scope'])->group(function () {
    Route::prefix('organizations')->group(function () {
        Route::post('/{id}/invite', [OrganizationMemberController::class, 'invite']);
        Route::put('/{id}/settings', [OrganizationController::class, 'updateSettings']);
    });
    Route::prefix('boards')->group(function () {
        Route::post('/', [BoardController::class, 'store']);
        Route::get('/', [BoardController::class, 'index']);
        Route::get('/{board}', [BoardController::class, 'show']);
        Route::put('/{id}', [BoardController::class, 'update']);

        Route::get('/{id}/columns', [ColumnController::class, 'index']);
    });
    Route::prefix('columns')->group(function () {
        Route::post('/', [ColumnController::class, 'store']);
        Route::put('/reorder', [ColumnController::class, 'reorder']);
        Route::put('/{id}', [ColumnController::class, 'update']);
        Route::delete('/{id}', [ColumnController::class, 'destroy']);
        Route::get('/{columnId}/cards', [CardController::class, 'index']);
        Route::post('/{columnId}/cards', [CardController::class, 'store']);
    });
    Route::prefix('cards')->group(function () {
        Route::put('/{id}', [CardController::class, 'update']);
        Route::delete('/{id}', [CardController::class, 'destroy']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/{organizationId}/members', [UserController::class, 'show']);
    });
});