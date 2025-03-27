<?php

namespace App\Utils;

class PasswordUtils
{
    public static function isPasswordValid(string $password): bool
    {
        return strlen($password) >= 5 && preg_match('/[A-Z]/', $password) && preg_match('/\d/', $password);
    }

}