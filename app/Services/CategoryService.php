<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    /**
     * Get all active categories.
     */
    public function getAll(): Collection
    {
        return Category::where('status', 'active')
            ->withCount('products')
            ->latest()
            ->get();
    }

    /**
     * Get category by ID.
     */
    public function getById(int $id): Category
    {
        return Category::with(['products' => function ($q) {
            $q->where('status', 'active')->where('is_available', true);
        }])->findOrFail($id);
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data, ?UploadedFile $image = null): Category
    {
        $categoryData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ];

        if ($image) {
            $categoryData['image_url'] = $image->store('categories/images', 'public');
        }

        return Category::create($categoryData);
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $categoryData = [
            'name' => $data['name'] ?? $category->name,
            'description' => $data['description'] ?? $category->description,
            'status' => $data['status'] ?? $category->status,
        ];

        if ($image) {
            if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
                Storage::disk('public')->delete($category->image_url);
            }
            $categoryData['image_url'] = $image->store('categories/images', 'public');
        }

        $category->update($categoryData);

        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): bool
    {
        if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
            Storage::disk('public')->delete($category->image_url);
        }

        return (bool) $category->delete();
    }
}
