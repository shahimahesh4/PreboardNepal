<?php

namespace App\Livewire;

use Livewire\Component;

class Account extends Component
{
    public string $name = '';

    public string $grade = '12';

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->grade = auth()->user()->grade;
    }

    public function save()
    {
        $data = $this->validate(['name' => 'required|string|max:100', 'grade' => 'required|in:10,11,12']);
        auth()->user()->update($data);
        session()->flash('status', 'Your profile has been updated.');
    }

    public function render()
    {
        return view('livewire.account')->layout('components.layouts.app', ['title' => 'Your account']);
    }
}
