<?php

namespace App\Livewire;

use App\Actions\Learning\AttemptService;
use App\Models\Attempt;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PracticeRunner extends Component
{
    #[Locked]
    public int $attemptId;

    #[Locked]
    public int $revision = 0;

    public int $current = 0;

    public bool $confirmSubmit = false;

    public function mount(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 404);
        $this->attemptId = $attempt->id;
        $this->revision = $attempt->revision;
    }

    protected function attempt()
    {
        return Attempt::where('user_id', auth()->id())->with('exam.subject')->findOrFail($this->attemptId);
    }

    public function choose(int $answer, AttemptService $service)
    {
        $attempt = $service->save(auth()->user(), $this->attemptId, $this->current, $answer, $this->revision);
        $this->revision = $attempt->revision;
    }

    public function goTo(int $index)
    {
        $this->current = max(0, min($this->attempt()->total - 1, $index));
    }

    public function submit(AttemptService $service)
    {
        $service->submit(auth()->user(), $this->attemptId);
    }

    public function heartbeat(AttemptService $service)
    {
        $this->attempt();
        $service->expire($this->attemptId);
    }

    public function render()
    {
        app(AttemptService::class)->expire($this->attemptId);
        $attempt = $this->attempt();
        $this->current = max(0, min($attempt->total - 1, $this->current));
        $questions = collect($attempt->snapshot['questions'])->map(fn ($q) => $attempt->status === 'submitted' ? $q : array_intersect_key($q, array_flip(['prompt', 'options'])))->all();

        return view('livewire.practice-runner', ['attempt' => $attempt, 'questions' => $questions])->layout('components.layouts.app', ['title' => 'Practice workspace']);
    }
}
