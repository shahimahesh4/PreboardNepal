<?php

namespace App\Actions\Learning;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttemptService
{
    public function start(User $user, Exam $exam): Attempt
    {
        abort_unless($exam->status === 'published' && $exam->subject->is_active, 404);

        return DB::transaction(function () use ($user, $exam) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $existing = Attempt::where('user_id', $user->id)->where('exam_id', $exam->id)->where('status', 'active')->first();
            if ($existing) {
                return $existing;
            }
            abort_if(empty($exam->questions), 422, 'This practice set has no questions.');

            return Attempt::create(['user_id' => $user->id, 'exam_id' => $exam->id, 'snapshot' => ['title' => $exam->title, 'version' => $exam->version, 'questions' => $exam->questions], 'answers' => [], 'total' => count($exam->questions), 'started_at' => now(), 'deadline_at' => now()->addMinutes($exam->duration_minutes)]);
        });
    }

    public function save(User $user, int $id, int $question, int $answer, int $revision): Attempt
    {
        return DB::transaction(function () use ($user, $id, $question, $answer, $revision) {
            $attempt = Attempt::where('user_id', $user->id)->lockForUpdate()->findOrFail($id);
            if ($attempt->status !== 'active') {
                return $attempt;
            }
            if (now()->gte($attempt->deadline_at)) {
                return $this->finalize($attempt);
            }
            if ($revision !== $attempt->revision) {
                throw ValidationException::withMessages(['answer' => 'A newer answer was saved in another tab. Reload before continuing.']);
            }
            $questions = $attempt->snapshot['questions'];
            if (! isset($questions[$question]['options'][$answer])) {
                throw ValidationException::withMessages(['answer' => 'Select a valid answer.']);
            }
            $answers = $attempt->answers;
            $answers[$question] = $answer;
            $attempt->update(['answers' => $answers, 'revision' => $revision + 1, 'saved_at' => now()]);

            return $attempt->refresh();
        });
    }

    public function submit(User $user, int $id): Attempt
    {
        return DB::transaction(fn () => $this->finalize(Attempt::where('user_id', $user->id)->lockForUpdate()->findOrFail($id)));
    }

    public function expire(int $id): void
    {
        DB::transaction(function () use ($id) {
            $attempt = Attempt::lockForUpdate()->find($id);
            if ($attempt && $attempt->status === 'active' && now()->gte($attempt->deadline_at)) {
                $this->finalize($attempt);
            }
        });
    }

    private function finalize(Attempt $attempt): Attempt
    {
        if ($attempt->status !== 'active') {
            return $attempt;
        }
        $score = 0;
        foreach ($attempt->snapshot['questions'] as $i => $q) {
            if (isset($attempt->answers[$i]) && (int) $attempt->answers[$i] === (int) $q['correct']) {
                $score++;
            }
        }
        $attempt->update(['status' => 'submitted', 'score' => $score, 'submitted_at' => now()]);

        return $attempt->refresh();
    }
}
