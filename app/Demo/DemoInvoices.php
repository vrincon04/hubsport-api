<?php

namespace App\Demo;

final class DemoInvoices
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return DemoFixtures::billing()['invoices'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function withDownloadUrls(): array
    {
        return array_map(
            fn (array $invoice): array => array_merge($invoice, [
                'download_url' => route('demo:invoices.pdf', ['invoice' => $invoice['id']]),
            ]),
            self::all(),
        );
    }
}
