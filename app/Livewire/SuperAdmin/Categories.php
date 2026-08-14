<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Category;

#[Layout('layouts.superadmin')]
class Categories extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        session()->flash('message', 'Create new category');
    }

    public function editCategory($id)
    {
        session()->flash('message', 'Edit category #' . $id);
    }

    public function confirmDelete($id)
    {
        session()->flash('message', 'Delete category #' . $id);
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        session()->flash('message', 'Status kategori berhasil diubah');
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('helps')
            ->latest()
            ->paginate($this->perPage);

        return view('superadmin.categories', compact('categories'));
    }
}
