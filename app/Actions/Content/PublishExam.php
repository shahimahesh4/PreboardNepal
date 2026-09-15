<?php

namespace App\Actions\Content;

use App\Models\AuditEntry;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishExam
{
    public function handle(User $reviewer, Exam $exam): void
    {
        abort_unless($reviewer->role === 'admin' && $reviewer->hasVerifiedEmail(), 403);
        DB::transaction(function () use ($reviewer, $exam) {
            $exam = Exam::lockForUpdate()->findOrFail($exam->id);
            if ($exam->author_id === $reviewer->id) {
                throw ValidationException::withMessages(['review' => 'A different administrator must review your own exam.']);
            }
            if (empty($exam->questions)) {
                throw ValidationException::withMessages(['review' => 'At least one question is required.']);
            }
            foreach ($exam->questions as $q) {
                if (empty($q['prompt']) || empty($q['explanation']) || ! isset($q['options'][$q['correct'] ?? -1])) {
                    throw ValidationException::withMessages(['review' => 'Every question requires valid options, an answer key, and an explanation.']);
                }
            }
            $exam->update(['status' => 'published', 'reviewer_id' => $reviewer->id, 'reviewed_at' => now()]);
            AuditEntry::create(['user_id' => $reviewer->id, 'action' => 'exam.published', 'resource' => 'exam:'.$exam->id, 'metadata' => ['version' => $exam->version]]);
        });
    }
}
