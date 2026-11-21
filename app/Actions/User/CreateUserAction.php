<?php

namespace App\Actions\User;

use App\Mail\GuardianConsentMail;
use App\Models\User;
use App\Support\AgePolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUserAction
{
    use AsAction;

    public function handle(array $data): User
    {
        $profile = $data['profile'];
        $name = $data['name'] ?? trim("{$profile['first_name']} {$profile['last_name']}");

        $user = User::firstOrCreate([
            'email' => $data['email'],
        ], [
            'name' => $name,
            'password' => $data['password'],
        ]);

        $existingProfile = $user->profile;
        if ($existingProfile?->sport_id) {
            unset($profile['sport_id'], $profile['sport_locked_at']);
        } elseif (! empty($profile['sport_id']) && empty($profile['sport_locked_at'])) {
            $profile['sport_locked_at'] = now();
        }

        unset(
            $profile['verified_badge'],
            $profile['verification_status'],
            $profile['verified_at'],
            $profile['phone_verified_at'],
            $profile['account_status'],
            $profile['guardian_token'],
            $profile['guardian_confirmed_at'],
        );

        $birthDate = ! empty($profile['birth_date'])
            ? Carbon::parse($profile['birth_date'])->startOfDay()
            : null;
        $type = $profile['profile_type'] ?? null;

        $guardianEmail = null;
        if (AgePolicy::requiresGuardian($birthDate, $type)) {
            $token = Str::random(48);
            $profile['account_status'] = 'pending_guardian';
            $profile['guardian_email'] = strtolower((string) ($profile['guardian_email'] ?? ''));
            $profile['guardian_token'] = $token;
            $profile['guardian_confirmed_at'] = null;
            $guardianEmail = $profile['guardian_email'];
        } elseif (in_array($type, ['recruiter', 'sponsor'], true)) {
            $profile['account_status'] = 'pending_review';
            unset($profile['guardian_email'], $profile['guardian_token']);
        } else {
            $profile['account_status'] = 'active';
            unset($profile['guardian_email'], $profile['guardian_token']);
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profile);
        $user = $user->load(['profile.country', 'profile.sport', 'avatar']);

        if ($guardianEmail && $user->profile?->guardian_token) {
            $confirmUrl = url('/api/v1/auth/guardian/confirm?token='.$user->profile->guardian_token);
            Mail::to($guardianEmail)->send(new GuardianConsentMail($user, $confirmUrl));
        }

        return $user;
    }
}
