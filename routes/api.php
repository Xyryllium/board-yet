<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/organizations/invitations/accept',
    [OrganizationMemberController::class, 'acceptInvitation']
);
Route::get('/organizations/invitations/details/{id}', 
    [OrganizationMemberController::class, 'listOrgDetails']
);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('organizations')->group(function () {
        Route::post('/', [OrganizationController::class, 'store']);
        Route::post('/{id}/invite', [OrganizationMemberController::class, 'invite']);
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
        Route::put('/{id}', [ColumnController::class, 'update']);
        Route::get('/{columnId}/cards', [CardController::class, 'index']);
        Route::post('/{columnId}/cards', [CardController::class, 'store']);
    });
    Route::prefix('cards')->group(function () {
        Route::put('/{id}', [CardController::class, 'update']);
        Route::delete('/{id}', [CardController::class, 'destroy']);
    });
});