<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

final class HistoricalReceiptEmailHash
{
    public static function of(string $email): string
    {
        return hash_hmac('sha256', strtolower(trim($email)), (string) config('app.key'));
    }
}
