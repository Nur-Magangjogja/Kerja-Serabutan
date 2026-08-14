<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $categoryId;

    // Form fields
    public $name = '';
    public $description = '';
    public $icon = '';
    public $color = '#3B82F6';
    public $is_active = true;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'description', 'icon', 'color', 'is_active', 'categoryId', 'editMode']);
        $this->color = '#3B82F6';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->icon = $category->icon;
        $this->color = $category->color;
        $this->is_active = $category->is_active;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        if ($this->editMode) {
            Category::findOrFail($this->categoryId)->update($validated);
        } else {
            Category::create($validated);
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'icon', 'color', 'is_active', 'categoryId', 'editMode']);
    }

    public function deleteCategory($id)
    {
        Category::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = Category::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.categories.index', [
            'categories' => $query->latest()->paginate(15),
        ]);
    }
}
