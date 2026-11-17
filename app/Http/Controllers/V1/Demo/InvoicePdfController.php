<?php

namespace App\Http\Controllers\V1\Demo;

use App\Demo\DemoAccess;
use App\Demo\DemoFixtures;
use App\Demo\DemoInvoices;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvoicePdfController extends Controller
{
    public function __invoke(Request $request, string $invoice): Response
    {
        DemoAccess::assert($request);
        $data = DemoInvoices::find($invoice);
        abort_unless($data, Response::HTTP_NOT_FOUND, 'Factura no encontrada');

        $content = Pdf::loadView('demo.invoice', [
            'invoice' => $data,
            'subscription' => DemoFixtures::billing()['subscription'],
        ])->setPaper('a4')->output();

        abort_if(strlen($content) > 1024 * 1024, Response::HTTP_INTERNAL_SERVER_ERROR, 'La factura excede el tamaño permitido');

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $data['number']).'.pdf';

        return response($content, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
