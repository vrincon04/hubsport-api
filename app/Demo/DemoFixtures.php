<?php

namespace App\Demo;

final class DemoFixtures
{
    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        return [
            'billing' => self::billing(),
            'groups' => self::groups(),
            'collaborations' => self::collaborations(),
            'store_products' => self::products(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function billing(): array
    {
        return [
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
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function groups(): array
    {
        return [
            [
                'id' => 'grp_demo_runners',
                'name' => 'Atletas de Alto Rendimiento',
                'description' => 'Velocistas y entrenadores que comparten marcas, planes y clínicas abiertas.',
                'members_count' => 128,
                'role' => 'member',
                'image_url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba6851?auto=format&fit=crop&w=1200&q=80',
                'posts' => [
                    [
                        'id' => 'gp_post_1',
                        'author' => 'Laura Méndez',
                        'body' => 'Mañana sesión de arranques a las 7:00 en la pista del Centro Olímpico.',
                        'created_at' => '2026-09-01T10:00:00Z',
                    ],
                    [
                        'id' => 'gp_post_2',
                        'author' => 'Andrés Castillo',
                        'body' => 'Subí el registro de 200 m. ¿Alguien se apunta al relevo del sábado?',
                        'created_at' => '2026-09-02T15:30:00Z',
                    ],
                ],
            ],
            [
                'id' => 'grp_demo_coaches',
                'name' => 'Cuerpo técnico Caribe',
                'description' => 'Coordinación de sesiones, marcas y calendario competitivo.',
                'members_count' => 36,
                'role' => 'visitor',
                'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=1200&q=80',
                'posts' => [
                    [
                        'id' => 'gp_post_3',
                        'author' => 'Camila Ortiz',
                        'body' => 'Publicamos la pauta de fuerza para la semana 12. Revisen cargas antes del jueves.',
                        'created_at' => '2026-09-03T09:00:00Z',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function collaborations(): array
    {
        return [
            [
                'id' => 'col_demo_training',
                'title' => 'Clínica deportiva juvenil',
                'description' => 'Apoyo técnico para atletas de 14 a 18 años.',
                'status' => 'open',
                'organization' => 'Fundación Impulso Deportivo',
                'partner' => 'Fundación Impulso Deportivo',
                'sport' => 'Atletismo',
                'benefits' => 'Kit de entrenamiento, visibilidad en redes de la fundación y plaza en la clínica.',
                'requirements' => 'Compromiso de 8 semanas y reporte de marcas quincenal.',
                'contact' => 'alianzas@impulso-deportivo.demo',
                'demo' => true,
            ],
            [
                'id' => 'col_demo_media',
                'title' => 'Cobertura del encuentro nacional',
                'description' => 'Producción de contenido con el Club Horizonte.',
                'status' => 'in_progress',
                'organization' => 'Club Horizonte',
                'partner' => 'Club Horizonte',
                'sport' => 'Comunicación deportiva',
                'benefits' => 'Credencial de prensa simulada y mención en la cobertura del encuentro.',
                'requirements' => 'Disponibilidad el fin de semana del encuentro nacional.',
                'contact' => 'prensa@clubhorizonte.demo',
                'demo' => true,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function products(): array
    {
        return [
            [
                'id' => 'prd_demo_recovery',
                'name' => 'Kit de recuperación',
                'description' => 'Rodillo, bandas y guía de movilidad para después de pista.',
                'price' => 49.90,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'purchasable' => true,
                'image_url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80',
                'variants' => ['Único'],
                'affiliate_url' => null,
                'demo' => true,
            ],
            [
                'id' => 'prd_demo_spikes',
                'name' => 'Clavos de pista 400 m',
                'description' => 'Calzado técnico para curva y recta. Entrega simulada en 48 horas.',
                'price' => 89.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'purchasable' => true,
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                'variants' => ['38', '39', '40', '41', '42'],
                'affiliate_url' => null,
                'demo' => true,
            ],
        ];
    }
}
