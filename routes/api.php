<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('organizations')->group(function () {
        Route::post('/', [OrganizationController::class, 'store']);
        Route::post('/{id}/invite', [OrganizationMemberController::class, 'invite']);
        Route::post('/invitations/accept', [OrganizationMemberController::class, 'acceptInvitation']);
    });
});