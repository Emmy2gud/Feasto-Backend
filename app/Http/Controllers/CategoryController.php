<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {}
    public function show($id)
    {}
    public function store(StoreCategoryRequest $request)
    {
        $user = auth()->user();
        if (! $user->vendor) {
            return response()->json([
                'message' => 'You do not have a vendor associated with your account.',
            ], 404);
        }

        $validatedData = $request->validated();

        $CategoryData = [
            'name'        => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'status'      => $validatedData['status'] ?? 'active',
        ];

        if ($request->hasFile('image_url')) {
            $CategoryData['image_url'] = $request->file('image_url')->store('categories/images', 'public');

        }
        $category = $user->vendor->categories()->create($CategoryData);
        return response()->json([
            'message'  => 'Category created successfully.',
            'category' => $category,
        ], 201);

    }
    public function update(UpdateCategoryRequest $request, $id)
    {
        $user   = auth()->user();
        $vendor = $user->vendor;
        if ($user->id !== $vendor->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $user = auth()->user();
        if (! $user->vendor) {
            return response()->json([
                'message' => 'You do not have a vendor associated with your account.',
            ], 404);
        }

        $validatedData = $request->validated();

        $CategoryData = [
            'name'        => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'status'      => $validatedData['status'] ?? 'active',
        ];
              if ($request->hasFile('image_url')) {
            if ($vendor->profile_picture && Storage::disk('public')->exists($vendor->image_url)) {
                Storage::disk('public')->delete($vendor->image_url);
            }

                $CategoryData['image_url'] = $request->file('image_url')->store('categories/images', 'public');
        }

        $category = $user->vendor->categories()->create($CategoryData);
        return response()->json([
            'message'  => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }
    public function destroy($id)
    {}
}
