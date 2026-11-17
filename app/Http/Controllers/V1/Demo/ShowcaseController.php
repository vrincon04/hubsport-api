<?php

namespace App\Http\Controllers\V1\Demo;

use App\Demo\DemoAccess;
use App\Demo\DemoFixtures;
use App\Demo\DemoInvoices;
use App\Demo\DemoOverlay;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = DemoAccess::assert($request);
        $overlay = DemoOverlay::get($user->id);
        $joined = collect($overlay['joined_groups'] ?? []);
        $extraPosts = collect($overlay['group_posts'] ?? []);

        $groups = collect(DemoFixtures::groups())->map(function (array $group) use ($joined, $extraPosts) {
            $id = $group['id'];
            $isJoined = $joined->contains($id);
            $posts = array_values(array_merge(
                $group['posts'] ?? [],
                $extraPosts->where('group_id', $id)->values()->all()
            ));

            return array_merge($group, [
                'joined' => $isJoined,
                'role' => $isJoined ? ($group['role'] === 'visitor' ? 'member' : $group['role']) : 'visitor',
                'posts' => $posts,
            ]);
        })->all();

        $billing = DemoFixtures::billing();
        $billing['invoices'] = DemoInvoices::withDownloadUrls();

        return response()->json([
            'data' => [
                'billing' => $billing,
                'groups' => $groups,
                'collaborations' => DemoFixtures::collaborations(),
                'store_products' => DemoFixtures::products(),
                'orders' => $overlay['orders'] ?? [],
                'sponsorship_requests' => $overlay['sponsorship_requests'] ?? [],
            ],
        ]);
    }
}
