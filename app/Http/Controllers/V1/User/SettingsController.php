<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    private function defaults(): array
    {
        return [
            'notifications' => [
                'pushEnabled' => true,
                'emailEnabled' => false,
                'messagesEnabled' => true,
                'opportunitiesEnabled' => true,
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
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $settings = Auth::user()->userSettings ?: UserSettings::create([
            'user_id' => Auth::id(),
            ...$this->defaults(),
        ]);

        return response()->json([
            'data' => $this->serializeSettings($settings),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'notifications' => 'sometimes|array',
            'privacy' => 'sometimes|array',
            'security' => 'sometimes|array',
            'privacy.publicProfile' => 'sometimes|boolean',
            'privacy.showStats' => 'sometimes|boolean',
            'privacy.allowMessagesFromAnyone' => 'sometimes|boolean',
            'notifications.pushEnabled' => 'sometimes|boolean',
            'notifications.emailEnabled' => 'sometimes|boolean',
            'notifications.messagesEnabled' => 'sometimes|boolean',
            'notifications.opportunitiesEnabled' => 'sometimes|boolean',
            'notifications.tipsEnabled' => 'sometimes|boolean',
            'security.is2FAEnabled' => 'sometimes|boolean',
        ]);

        $settings = Auth::user()->userSettings ?: UserSettings::create([
            'user_id' => Auth::id(),
            ...$this->defaults(),
        ]);

        $settings->update([
            'notifications' => array_replace($settings->notifications ?? [], $request->input('notifications', [])),
            'privacy' => array_replace($settings->privacy ?? [], $request->input('privacy', [])),
            'security' => array_replace($settings->security ?? [], $request->input('security', [])),
        ]);

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $this->serializeSettings($settings->fresh()),
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $user = Auth::user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'La contraseña actual no es correcta.'], 422);
        }

        $user->forceFill(['password' => $data['password']])->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    public function sessions(Request $request)
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => date('c', (int) $session->last_activity),
                'is_current' => $session->id === $request->session()->getId(),
            ]);

        return response()->json(['data' => $sessions]);
    }

    public function destroySession(Request $request, string $id)
    {
        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    private function serializeSettings(UserSettings $settings): array
    {
        $defaults = $this->defaults();

        return [
            'id' => $settings->id,
            'user_id' => $settings->user_id,
            'notifications' => array_replace($defaults['notifications'], $settings->notifications ?? []),
            'privacy' => array_replace($defaults['privacy'], $settings->privacy ?? []),
            'security' => array_replace($defaults['security'], $settings->security ?? []),
            'created_at' => $settings->created_at,
            'updated_at' => $settings->updated_at,
        ];
    }
}