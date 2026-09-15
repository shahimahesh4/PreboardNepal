<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\StudyDocument;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    private function featuredDocuments()
    {
        return StudyDocument::published()->whereIn('id', function ($query) {
            $query->from('study_documents')->join('chapters', 'chapters.id', '=', 'study_documents.chapter_id')->selectRaw('MIN(study_documents.id)')->where('study_documents.status', 'published')->groupBy('chapters.subject_id');
        })->with('chapter.subject')->limit(3)->get();
    }

    public function home()
    {
        return view('pages.home', ['subjects' => Subject::where('is_active', true)->withCount(['documents' => fn ($q) => $q->published()])->get(), 'documents' => $this->featuredDocuments()]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        $attempts = $user->attempts()->with('exam.subject')->latest()->take(5)->get();
        $completed = $user->attempts()->where('status', 'submitted');
        $totals = (clone $completed)->selectRaw('SUM(score) as score_sum, SUM(total) as total_sum')->first();
        $recentId = DB::table('reading_events')->where('user_id', $user->id)->orderByDesc('last_read_at')->value('study_document_id');

        return view('pages.dashboard', ['subjects' => Subject::where('is_active', true)->withCount(['documents' => fn ($q) => $q->published()])->get(), 'documents' => $this->featuredDocuments(), 'attempts' => $attempts, 'completedCount' => (clone $completed)->count(), 'accuracy' => $totals->total_sum ? round($totals->score_sum / $totals->total_sum * 100) : null, 'savedCount' => $user->bookmarks()->count(), 'continueDocument' => $recentId ? StudyDocument::published()->find($recentId) : StudyDocument::published()->first()]);
    }

    public function subject(Subject $subject)
    {
        abort_unless($subject->is_active, 404);

        return view('pages.subject', ['subject' => $subject->load(['chapters' => fn ($q) => $q->withCount(['documents' => fn ($q) => $q->published()])]), 'documents' => $subject->documents()->published()->with('chapter.subject')->get(), 'exams' => $subject->exams()->where('status', 'published')->get()]);
    }

    public function practice()
    {
        return view('pages.practice', ['exams' => Exam::where('status', 'published')->whereHas('subject', fn ($q) => $q->where('is_active', true))->with('subject')->get()]);
    }
}
