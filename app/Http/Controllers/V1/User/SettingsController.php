<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show()
    {
        $settings = Auth::user()->userSettings ?: UserSettings::create([
            'user_id' => Auth::id(),
            'notifications' => [
                'pushEnabled' => true,
                'emailEnabled' => false,
                'messagesEnabled' => true,
                'eventsEnabled' => true,
                'tipsEnabled' => true,
            ],
            'privacy' => [
                'publicProfile' => true,
                'showStats' => true,
                'allowMessagesFromAnyone' => false,
            ],
            'security' => [
                'is2FAEnabled' => false,
            ],
        ]);

        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $settings = Auth::user()->userSettings ?: UserSettings::create(['user_id' => Auth::id()]);

        $settings->update($request->only(['notifications', 'privacy', 'security']));

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $settings,
        ]);
    }
}