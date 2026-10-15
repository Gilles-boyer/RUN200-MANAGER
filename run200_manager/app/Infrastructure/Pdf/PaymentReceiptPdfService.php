<?php

namespace App\Infrastructure\Pdf;

use App\Models\PaymentReceipt;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptPdfService
{
    public function bytes(PaymentReceipt $receipt): string
    {
        return Pdf::loadView('pdf.payment-receipt', ['receipt' => $receipt])
            ->setPaper('a4', 'portrait')
            ->output();
    }
}
