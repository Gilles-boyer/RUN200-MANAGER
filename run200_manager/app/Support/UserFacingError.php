<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class UserFacingError
{
    public static function message(Throwable $exception, string $summary): string
    {
        $reference = strtoupper(Str::random(8));

        Log::error($summary, [
            'reference' => $reference,
            'exception' => $exception,
        ]);

        return $summary.' Réessayez. Si le problème persiste, contactez l’équipe avec le code '.$reference.'.';
    }
}
