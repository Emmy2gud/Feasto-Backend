<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();

        return response()->json([
            'message' => 'Categories retrieved successfully.',
            'categories' => $categories,
        ], 200);
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->getById($id);

        return response()->json([
            'message' => 'Category retrieved successfully.',
            'category' => $category,
        ], 200);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory(
            $request->validated(),
            $request->file('image_url')
        );

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $updatedCategory = $this->categoryService->updateCategory(
            $category,
            $request->validated(),
            $request->file('image_url')
        );

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $updatedCategory,
        ], 200);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $this->categoryService->deleteCategory($category);

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);
    }
}
