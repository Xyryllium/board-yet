<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    protected function createUserWithToken(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;
        
        return ['user' => $user, 'token' => $token];
    }

    public function withApiToken(string $token): self
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ]);

        return $this;
    }
}
