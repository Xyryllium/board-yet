<?php

namespace App\Infrastructure\Persistence;

use App\Domain\PasswordReset\Repositories\PasswordResetRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    public function createOrUpdate(string $email, string $token): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );
    }

    public function findByEmail(string $email): ?array
    {
        $result = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        return $result ? (array) $result : null;
    }

    public function deleteByEmail(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
