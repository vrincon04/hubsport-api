<?php

namespace App\Support;

use App\Models\Profile;
use App\Models\User;

class RolePermissionPolicy
{
    public static function canWriteContent(?User $user): bool
    {
        $status = $user?->profile?->account_status ?? 'active';

        return ! in_array($status, ['pending_guardian', 'pending_review', 'suspended'], true);
    }

    public static function canPublishJobs(?User $user): bool
    {
        return self::canWriteContent($user)
            && ($user?->profile?->profile_type === 'recruiter')
            && (bool) $user?->profile?->verified_badge;
    }

    public static function canCreateEvents(?User $user): bool
    {
        $type = $user?->profile?->profile_type;

        return self::canWriteContent($user)
            && in_array($type, ['coach', 'recruiter', 'event_organizer'], true);
    }

    public static function canTalentSearch(?User $user): bool
    {
        $type = $user?->profile?->profile_type;

        return self::canWriteContent($user)
            && in_array($type, ['coach', 'recruiter'], true);
    }

    public static function isVerifiedRecruiter(?User $user): bool
    {
        $profile = $user?->profile;

        return $profile?->profile_type === 'recruiter'
            && (bool) $profile?->verified_badge;
    }

    public static function canViewMinorSensitiveFields(User $viewer, User $subject): bool
    {
        if ($viewer->id === $subject->id) {
            return true;
        }

        if (self::isVerifiedRecruiter($viewer)) {
            return true;
        }

        return $viewer->getConnectionStatusWith($subject) === 'accepted';
    }

    public static function canReviewReports(?User $user): bool
    {
        $type = $user?->profile?->profile_type;

        return self::canWriteContent($user)
            && (
                in_array($type, ['federation', 'event_organizer'], true)
                || ($user?->email === config('hubsport.moderator_email', 'admin@hubsport.test'))
            );
    }

    public static function denyUnless(bool $allowed, string $message = 'No autorizado para esta acción'): void
    {
        abort_unless($allowed, 403, $message);
    }
}
