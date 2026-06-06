<?php

namespace App\Helpers;

class Toast
{
    public static function success(string $message): array
    {
        return [
            'id' => uniqid(),
            'type' => 'success',
            'message' => $message,
        ];
    }

    public static function error(string $message): array
    {
        return [
            'id' => uniqid(),
            'type' => 'error',
            'message' => $message,
        ];
    }
}
