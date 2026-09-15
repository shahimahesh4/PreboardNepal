<?php

namespace App\Services;

use App\Models\StudyDocument;
use App\Models\User;

class DocumentAccess
{
    public function canRead(?User $user, StudyDocument $document): bool
    {
        if ($document->status !== 'published' || ! $document->chapter->subject->is_active) {
            return false;
        }
        if ($document->is_free) {
            return true;
        }

        return $user && $user->entitlements()->whereNull('revoked_at')->where('starts_at', '<=', now())->where('ends_at', '>', now())->exists();
    }
}
