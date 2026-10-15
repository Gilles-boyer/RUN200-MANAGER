<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Infrastructure\Pdf\PaymentReceiptPdfService;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Http\Response;

class DownloadReceiptController extends Controller
{
    public function __invoke(
        Payment $payment,
        PaymentReceiptPdfService $pdf
    ): Response {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $isOwner = $user->pilot !== null
            && $payment->registration !== null
            && $payment->registration->pilot_id === $user->pilot->id;
        abort_unless($isOwner || $user->can('payment.view'), 403);

        abort_unless($payment->canHaveReceipt(), 404);

        // Reading a receipt must never issue one for an unreviewed historical payment.
        $receipt = PaymentReceipt::query()->where('payment_id', $payment->id)->first();
        abort_unless($receipt !== null, 404);

        return response($pdf->bytes($receipt), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="recu-'.$receipt->number.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
