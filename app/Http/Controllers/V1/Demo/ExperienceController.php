<?php

namespace App\Http\Controllers\V1\Demo;

use App\Demo\DemoAccess;
use App\Demo\DemoFixtures;
use App\Demo\DemoOverlay;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ExperienceController extends Controller
{
    public function joinGroup(Request $request, string $id): JsonResponse
    {
        $user = DemoAccess::assert($request);
        $this->assertGroup($id);
        $state = DemoOverlay::get($user->id);
        $joined = collect($state['joined_groups'] ?? [])->push($id)->unique()->values()->all();
        $state['joined_groups'] = $joined;
        DemoOverlay::put($user->id, $state);

        return response()->json(['message' => 'Te uniste al grupo', 'data' => ['joined' => true]]);
    }

    public function leaveGroup(Request $request, string $id): JsonResponse
    {
        $user = DemoAccess::assert($request);
        $this->assertGroup($id);
        $state = DemoOverlay::get($user->id);
        $state['joined_groups'] = collect($state['joined_groups'] ?? [])
            ->reject(fn ($groupId) => $groupId === $id)
            ->values()
            ->all();
        DemoOverlay::put($user->id, $state);

        return response()->json(['message' => 'Saliste del grupo', 'data' => ['joined' => false]]);
    }

    public function postInGroup(Request $request, string $id): JsonResponse
    {
        $user = DemoAccess::assert($request);
        $this->assertGroup($id);
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $state = DemoOverlay::get($user->id);
        $post = [
            'id' => 'gp_user_'.Str::lower(Str::random(8)),
            'group_id' => $id,
            'author' => $user->name,
            'body' => $validated['body'],
            'created_at' => now()->toIso8601String(),
        ];
        $state = $this->appendCapped($state, 'group_posts', $post);
        DemoOverlay::put($user->id, $state);

        return response()->json(['message' => 'Publicación creada', 'data' => $post], Response::HTTP_CREATED);
    }

    public function checkout(Request $request): JsonResponse
    {
        $user = DemoAccess::assert($request);
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'variant' => ['nullable', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:180'],
        ]);

        $product = collect(DemoFixtures::products())->firstWhere('id', $validated['product_id']);
        abort_unless($product, Response::HTTP_NOT_FOUND, 'Producto no encontrado');

        $amount = (float) $product['price'];
        $commissionPercent = 12.0;
        $commissionAmount = round($amount * ($commissionPercent / 100), 2);

        $order = [
            'id' => 'ord_demo_'.Str::lower(Str::random(6)),
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'variant' => $validated['variant'] ?? ($product['variants'][0] ?? 'Único'),
            'amount' => $amount,
            'currency' => $product['currency'],
            'commission_percent' => $commissionPercent,
            'commission_amount' => $commissionAmount,
            'net_amount' => round($amount - $commissionAmount, 2),
            'recipient_name' => $validated['recipient_name'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'status' => 'confirmed',
            'created_at' => now()->toIso8601String(),
        ];

        $state = $this->appendCapped(DemoOverlay::get($user->id), 'orders', $order);
        DemoOverlay::put($user->id, $state);

        return response()->json([
            'message' => 'Pedido confirmado',
            'data' => $order,
        ], Response::HTTP_CREATED);
    }

    public function applySponsorship(Request $request, string $id): JsonResponse
    {
        $user = DemoAccess::assert($request);
        $this->assertSponsorship($id);
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $application = [
            'id' => 'spr_demo_'.Str::lower(Str::random(6)),
            'sponsorship_id' => $id,
            'message' => $validated['message'] ?? 'Solicito apoyo para la temporada competitiva.',
            'status' => 'received',
            'created_at' => now()->toIso8601String(),
        ];

        $state = $this->appendCapped(DemoOverlay::get($user->id), 'sponsorship_requests', $application);
        DemoOverlay::put($user->id, $state);

        return response()->json([
            'message' => 'Solicitud enviada',
            'data' => $application,
        ], Response::HTTP_CREATED);
    }

    private function assertGroup(string $id): void
    {
        $exists = collect(DemoFixtures::groups())->contains(fn (array $group) => $group['id'] === $id);
        abort_unless($exists, Response::HTTP_NOT_FOUND, 'Grupo no encontrado');
    }

    private function assertSponsorship(string $id): void
    {
        $exists = collect(DemoFixtures::collaborations())->contains(fn (array $item) => $item['id'] === $id);
        abort_unless($exists, Response::HTTP_NOT_FOUND, 'Patrocinio no encontrado');
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function appendCapped(array $state, string $key, array $item, int $max = 20): array
    {
        $items = array_values(array_merge($state[$key] ?? [], [$item]));
        abort_if(count($items) > $max, Response::HTTP_UNPROCESSABLE_ENTITY, 'Límite de acciones demo alcanzado');
        $state[$key] = $items;

        return $state;
    }
}
