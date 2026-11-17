<?php

namespace Tests\Feature\Demo;

use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoInvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);
    }

    public function test_only_demo_session_can_download_an_invoice_pdf(): void
    {
        $normal = User::factory()->create();
        $normalToken = $normal->createToken('normal', ['*'])->plainTextToken;

        $this->withToken($normalToken)
            ->get('/api/v1/demo/invoices/inv_demo_2026_08/pdf')
            ->assertForbidden();
    }

    public function test_demo_invoice_is_returned_as_a_non_cacheable_pdf(): void
    {
        $response = $this->withToken($this->demoToken())
            ->get('/api/v1/demo/invoices/inv_demo_2026_08/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('x-content-type-options', 'nosniff');

        $content = $response->getContent();
        $this->assertSame((string) strlen($content), $response->headers->get('content-length'));
        $cacheControl = $response->headers->get('cache-control', '');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertLessThanOrEqual(1024 * 1024, strlen($content));
    }

    public function test_invoice_template_contains_demo_notice_without_payment_secrets(): void
    {
        $invoice = \App\Demo\DemoInvoices::find('inv_demo_2026_08');
        $html = view('demo.invoice', [
            'invoice' => $invoice,
            'subscription' => \App\Demo\DemoFixtures::billing()['subscription'],
        ])->render();

        $this->assertStringContainsString('DEMO — SIN VALOR FISCAL', $html);
        $this->assertStringContainsString('DEMO-2026-008', $html);
        $this->assertStringNotContainsString('cvv', strtolower($html));
        $this->assertDoesNotMatchRegularExpression('/\b\d{13,19}\b/', $html);
    }

    public function test_unknown_invoice_returns_not_found(): void
    {
        $this->withToken($this->demoToken())
            ->get('/api/v1/demo/invoices/not-an-invoice/pdf')
            ->assertNotFound();
    }

    public function test_every_showcase_invoice_exposes_a_working_pdf_url(): void
    {
        $token = $this->demoToken();
        $invoices = $this->withToken($token)
            ->getJson('/api/v1/demo/showcase')
            ->assertOk()
            ->json('data.billing.invoices');

        foreach ($invoices as $invoice) {
            $this->assertNotEmpty($invoice['download_url']);
            $this->assertStringStartsWith(config('app.url'), $invoice['download_url']);
            $this->withToken($token)
                ->get($invoice['download_url'])
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    private function demoToken(): string
    {
        $user = User::query()->where('email', config('demo.user_email'))->firstOrFail();

        return $user->createToken('invoice-pdf', ['demo'])->plainTextToken;
    }
}
