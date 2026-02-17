<?php

namespace App\Livewire\TagManagement;

use App\Models\Tag;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';

    public bool $showEditModal = false;

    public ?int $editingTagId = null;

    public string $editingName = '';

    public function create(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tags,name'],
        ]);

        Tag::create(['name' => $this->name]);

        $this->reset('name');
    }

    public function startEdit(int $tagId): void
    {
        $tag = Tag::findOrFail($tagId);

        $this->editingTagId = $tag->id;
        $this->editingName = $tag->name;
        $this->showEditModal = true;
    }

    public function update(): void
    {
        $this->validate([
            'editingName' => ['required', 'string', 'max:255', 'unique:tags,name,'.$this->editingTagId],
        ]);

        $tag = Tag::findOrFail($this->editingTagId);
        $tag->update(['name' => $this->editingName]);

        $this->showEditModal = false;
        $this->reset('editingTagId', 'editingName');
    }

    public function render()
    {
        return view('livewire.tag-management.index', [
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }
}
