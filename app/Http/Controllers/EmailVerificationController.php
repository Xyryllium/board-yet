<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Events\EmailVerificationSent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ]);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            Log::channel('auth')->info("Resend verification requested for already verified user", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        Log::channel('auth')->info("Resending email verification", [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        event(new EmailVerificationSent($user));

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent'
        ]);
    }

    public function verifyFromEmail(Request $request): JsonResponse
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');

        Log::channel('auth')->info("Email verification attempt", [
            'user_id' => $userId,
            'hash' => $hash,
        ]);

        if (!hash_equals((string) $hash, sha1(User::findOrFail($userId)->email))) {
            Log::channel('auth')->warning("Invalid verification link", [
                'user_id' => $userId,
                'hash' => $hash,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 400);
        }

        $user = User::findOrFail($userId);

        if ($user->hasVerifiedEmail()) {
            Log::channel('auth')->info("Email already verified", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            Log::channel('auth')->info("Email verification successful", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    }
}
