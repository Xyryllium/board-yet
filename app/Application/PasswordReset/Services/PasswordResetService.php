<?php

namespace App\Application\PasswordReset\Services;

use RuntimeException;
use App\Domain\PasswordReset\Exceptions\InvalidPasswordResetTokenException;
use App\Domain\PasswordReset\Exceptions\PasswordResetTokenExpiredException;
use App\Domain\PasswordReset\Repositories\PasswordResetRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function __construct(
        private PasswordResetRepositoryInterface $passwordResetRepo,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function requestPasswordReset(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new RuntimeException('User not found');
        }

        $token = Str::random(64);

        $this->passwordResetRepo->createOrUpdate($email, $token);

        Mail::to($email)->send(new PasswordResetMail($token, $email));
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $passwordReset = $this->passwordResetRepo->findByEmail($email);

        if (!$passwordReset) {
            throw InvalidPasswordResetTokenException::notFound();
        }

        if (!$this->isTokenValid($token, $passwordReset['token'])) {
            throw InvalidPasswordResetTokenException::invalid();
        }

        if ($this->isTokenExpired($passwordReset['created_at'])) {
            $this->passwordResetRepo->deleteByEmail($email);
            throw PasswordResetTokenExpiredException::expired();
        }

        $this->updateUserPassword($email, $password);
        $this->passwordResetRepo->deleteByEmail($email);
    }

    private function isTokenValid(string $token, string $hashedToken): bool
    {
        return Hash::check($token, $hashedToken);
    }

    private function isTokenExpired(string $createdAt): bool
    {
        return \Carbon\Carbon::parse($createdAt)->diffInMinutes(now()) > 60;
    }

    private function updateUserPassword(string $email, string $password): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new RuntimeException('User not found');
        }

        $user->password = Hash::make($password);
        $user->save();
    }
}
