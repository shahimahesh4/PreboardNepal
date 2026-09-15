<?php

namespace App\Livewire;

use App\Models\Bookmark;
use App\Models\StudyDocument;
use App\Models\Subject;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Library extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $subject = '';

    #[Url]
    public string $type = '';

    #[Locked]
    public bool $savedOnly = false;

    public function mount(bool $savedOnly = false)
    {
        $this->savedOnly = $savedOnly;
        if ($savedOnly) {
            abort_unless(auth()->check(), 403);
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['q', 'subject', 'type'])) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['q', 'subject', 'type']);
        $this->resetPage();
    }

    public function toggleSave(int $id)
    {
        if (! auth()->check()) {
            return $this->redirectRoute('login');
        }
        StudyDocument::published()->findOrFail($id);
        $existing = Bookmark::where('user_id', auth()->id())->where('study_document_id', $id)->first();
        if ($existing) {
            $existing->delete();
        } else {
            Bookmark::firstOrCreate(['user_id' => auth()->id(), 'study_document_id' => $id]);
        }
    }

    public function render()
    {
        $q = mb_substr(trim($this->q), 0, 200);
        $query = StudyDocument::published()->with('chapter.subject')->when($q, fn ($query) => $query->where(fn ($s) => $s->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%")))
            ->when($this->subject, fn ($query) => $query->whereHas('chapter.subject', fn ($s) => $s->where('slug', $this->subject)))->when($this->type, fn ($query) => $query->where('type', $this->type));
        if ($this->savedOnly) {
            $query->whereHas('bookmarks', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return view('livewire.library', ['documents' => $query->latest()->paginate(9), 'subjects' => Subject::where('is_active', true)->get(), 'savedIds' => auth()->user()?->bookmarks()->pluck('study_document_id')->all() ?? []])->layout('components.layouts.app', ['title' => $this->savedOnly ? 'Saved resources' : 'Study library']);
    }
}
