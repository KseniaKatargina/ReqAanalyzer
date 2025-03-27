<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function createToken(string $email): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 18000;
        $payload = [
            'email' => $email,
            'iat' => $issuedAt,
            'exp' => $expirationTime,
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function decodeToken(string $token): object
    {
        return JWT::decode($token, new Key($this->secretKey, 'HS256'));
    }

    public function validateToken(string $token): bool
    {
        try {
            $this->decodeToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}