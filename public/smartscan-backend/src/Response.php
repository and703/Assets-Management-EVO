<?php
// src/Response.php
namespace App;

final class Response
{
    public static function json(array|string $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo \is_string($data)
            ? json_encode(['message' => $data], JSON_UNESCAPED_UNICODE)
            : json_encode($data,           JSON_UNESCAPED_UNICODE);

        exit;
    }
}
