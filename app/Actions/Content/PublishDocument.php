<?php

namespace App\Actions\Content;

use App\Models\AuditEntry;
use App\Models\StudyDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishDocument
{
    public function handle(User $reviewer, StudyDocument $document): void
    {
        abort_unless($reviewer->role === 'admin' && $reviewer->hasVerifiedEmail(), 403);
        DB::transaction(function () use ($reviewer, $document) {
            $document = StudyDocument::lockForUpdate()->findOrFail($document->id);
            if ($document->author_id === $reviewer->id) {
                throw ValidationException::withMessages(['review' => 'A different administrator must review your own content.']);
            }
            if (! trim($document->rights_statement) || ! trim($document->body)) {
                throw ValidationException::withMessages(['review' => 'Content and rights evidence are required before publication.']);
            }
            $document->update(['status' => 'published', 'reviewer_id' => $reviewer->id, 'reviewed_at' => now()]);
            AuditEntry::create(['user_id' => $reviewer->id, 'action' => 'document.published', 'resource' => 'document:'.$document->id, 'metadata' => ['revision' => $document->revision]]);
        });
    }
}
