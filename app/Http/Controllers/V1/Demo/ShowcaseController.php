<?php

namespace App\Http\Controllers\V1\Demo;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowcaseController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            config('demo.enabled')
                && $user
                && hash_equals((string) config('demo.user_email'), (string) $user->email)
                && $user->tokenCan('demo')
                && ! $user->tokenCan('*'),
            Response::HTTP_FORBIDDEN
        );

        return response()->json([
            'data' => [
                'billing' => [
                    'subscription' => [
                        'name' => 'HubSport Pro Demo',
                        'plan' => 'HubSport Pro Demo',
                        'status' => 'active',
                        'renews_at' => '2026-12-01',
                        'renewal_date' => '2026-12-01',
                        'amount' => 0,
                        'price' => '0',
                        'currency' => 'USD',
                        'demo' => true,
                    ],
                    'payment_methods' => [
                        [
                            'id' => 'pm_demo_visa',
                            'brand' => 'visa',
                            'last4' => '4242',
                            'expires' => '12/30',
                            'expiry' => '12/30',
                            'expires_at' => '12/30',
                            'holder' => 'USUARIO DEMO',
                            'is_default' => true,
                            'demo' => true,
                        ],
                        [
                            'id' => 'pm_demo_mastercard',
                            'brand' => 'mastercard',
                            'last4' => '4444',
                            'expires' => '08/29',
                            'expiry' => '08/29',
                            'expires_at' => '08/29',
                            'holder' => 'USUARIO DEMO',
                            'is_default' => false,
                            'demo' => true,
                        ],
                    ],
                    'invoices' => [
                        [
                            'id' => 'inv_demo_2026_08',
                            'number' => 'DEMO-2026-008',
                            'status' => 'paid',
                            'issued_at' => '2026-08-01',
                            'date' => '2026-08-01',
                            'amount' => 0,
                            'currency' => 'USD',
                            'description' => 'Suscripción HubSport Pro Demo',
                            'download_url' => null,
                            'demo' => true,
                        ],
                        [
                            'id' => 'inv_demo_2026_09',
                            'number' => 'DEMO-2026-009',
                            'status' => 'paid',
                            'issued_at' => '2026-09-01',
                            'date' => '2026-09-01',
                            'amount' => 0,
                            'currency' => 'USD',
                            'description' => 'Renovación simulada',
                            'download_url' => null,
                            'demo' => true,
                        ],
                    ],
                ],
                'groups' => [
                    [
                        'id' => 'grp_demo_runners',
                        'name' => 'Atletas de Alto Rendimiento',
                        'description' => 'Grupo de velocistas y entrenadores de la comunidad demo.',
                        'members_count' => 128,
                        'role' => 'member',
                        'demo' => true,
                    ],
                    [
                        'id' => 'grp_demo_coaches',
                        'name' => 'Cuerpo técnico Caribe',
                        'description' => 'Coordinación de sesiones, marcas y calendario competitivo.',
                        'members_count' => 36,
                        'role' => 'admin',
                        'demo' => true,
                    ],
                ],
                'collaborations' => [
                    [
                        'id' => 'col_demo_training',
                        'title' => 'Clínica deportiva juvenil',
                        'description' => 'Apoyo técnico para atletas de 14 a 18 años.',
                        'status' => 'open',
                        'organization' => 'Fundación Impulso Deportivo',
                        'partner' => 'Fundación Impulso Deportivo',
                        'demo' => true,
                    ],
                    [
                        'id' => 'col_demo_media',
                        'title' => 'Cobertura del encuentro nacional',
                        'description' => 'Producción de contenido con el Club Horizonte.',
                        'status' => 'in_progress',
                        'organization' => 'Club Horizonte',
                        'partner' => 'Club Horizonte',
                        'demo' => true,
                    ],
                ],
                'store_products' => [
                    [
                        'id' => 'prd_demo_recovery',
                        'name' => 'Kit de recuperación',
                        'description' => 'Rodillo, bandas y guía de movilidad. Vista previa, no comprable.',
                        'price' => 49.90,
                        'currency' => 'USD',
                        'stock_status' => 'preview',
                        'purchasable' => false,
                        'affiliate_url' => null,
                        'demo' => true,
                    ],
                    [
                        'id' => 'prd_demo_spikes',
                        'name' => 'Clavos de pista 400 m',
                        'description' => 'Calzado técnico de demostración. Sin checkout.',
                        'price' => 89.00,
                        'currency' => 'USD',
                        'stock_status' => 'preview',
                        'purchasable' => false,
                        'affiliate_url' => null,
                        'demo' => true,
                    ],
                ],
            ],
        ]);
    }
}
