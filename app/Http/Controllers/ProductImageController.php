<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Upload an additional gallery image for a product.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $user = $request->user();

        if (! $user->vendor || ($product->vendor_id !== $user->vendor->id && $user->role !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = $request->file('image')->store('vendors/products/gallery', 'public');

        $image = $product->images()->create([
            'image_url' => $path,
        ]);

        return response()->json([
            'message' => 'Product image uploaded successfully.',
            'image' => $image,
        ], 201);
    }

    /**
     * Delete a product gallery image.
     */
    public function destroy(int $id): JsonResponse
    {
        $image = ProductImage::findOrFail($id);
        $user = auth()->user();

        if (! $user->vendor || ($image->product->vendor_id !== $user->vendor->id && $user->role !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
            Storage::disk('public')->delete($image->image_url);
        }

        $image->delete();

        return response()->json(['message' => 'Product image deleted successfully.'], 200);
    }
}
