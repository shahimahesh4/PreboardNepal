<?php

namespace App\Livewire;

use App\Models\Bookmark;
use App\Models\ContentReport;
use App\Models\StudyDocument;
use App\Services\DocumentAccess;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Reader extends Component
{
    #[Locked]
    public int $documentId;

    public bool $reportOpen = false;

    public string $category = 'accuracy';

    public string $message = '';

    public function mount(StudyDocument $document)
    {
        $this->documentId = $document->id;
        $document = $this->document();
        if (auth()->check() && app(DocumentAccess::class)->canRead(auth()->user(), $document)) {
            DB::table('reading_events')->updateOrInsert(['user_id' => auth()->id(), 'study_document_id' => $document->id], ['last_read_at' => now()]);
        }
    }

    protected function document()
    {
        return StudyDocument::published()->with('chapter.subject')->findOrFail($this->documentId);
    }

    public function toggleSave()
    {
        if (! auth()->check()) {
            return $this->redirectRoute('login');
        } $document = $this->document();
        $saved = Bookmark::where('user_id', auth()->id())->where('study_document_id', $document->id)->first();
        if ($saved) {
            $saved->delete();
        } else {
            Bookmark::firstOrCreate(['user_id' => auth()->id(), 'study_document_id' => $document->id]);
        }
    }

    public function report()
    {
        abort_unless(auth()->check(), 403);
        $this->document();
        $data = $this->validate(['category' => 'required|in:accuracy,syllabus,rights,privacy,other', 'message' => 'required|string|min:10|max:2000']);
        ContentReport::create($data + ['user_id' => auth()->id(), 'study_document_id' => $this->documentId]);
        $this->reset(['message', 'reportOpen']);
        session()->flash('status', 'Thank you. Your report has been sent for review.');
    }

    public function render()
    {
        $document = $this->document();

        return view('livewire.reader', ['document' => $document, 'canRead' => app(DocumentAccess::class)->canRead(auth()->user(), $document), 'saved' => auth()->check() && Bookmark::where('user_id', auth()->id())->where('study_document_id', $document->id)->exists(), 'related' => $document->chapter->subject->exams()->where('status', 'published')->take(2)->get()])->layout('components.layouts.app', ['title' => $document->title]);
    }
}
