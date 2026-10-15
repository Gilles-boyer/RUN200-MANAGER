<?php

namespace App\Http\Controllers\Payments;

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Http\Controllers\Controller;
use App\Infrastructure\Pdf\PaymentReceiptPdfService;
use App\Models\Payment;
use Illuminate\Http\Response;
use InvalidArgumentException;

class DownloadReceiptController extends Controller
{
    public function __invoke(
        Payment $payment,
        IssuePaymentReceipt $issuer,
        PaymentReceiptPdfService $pdf
    ): Response {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $isOwner = $user->pilot !== null
            && $payment->registration !== null
            && $payment->registration->pilot_id === $user->pilot->id;
        abort_unless($isOwner || $user->can('payment.view'), 403);

        abort_unless($payment->canHaveReceipt(), 404);

        try {
            $receipt = $issuer->issue($payment, historical: true);
        } catch (InvalidArgumentException) {
            abort(422, 'Le reçu ne peut pas être généré avec les informations de ce paiement.');
        }

        return response($pdf->bytes($receipt), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="recu-'.$receipt->number.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
