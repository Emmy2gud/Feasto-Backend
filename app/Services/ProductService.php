<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Get paginated products with optional filters.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['vendor', 'category', 'addons'])
            ->where('status', 'active');

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get products belonging to a specific vendor.
     */
    public function getVendorProducts(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['addons', 'category'])
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a product by ID.
     */
    public function getById(int $id): Product
    {
        return Product::with(['vendor', 'category', 'addons', 'images'])->findOrFail($id);
    }

    /**
     * Create a new product with optional addons and image.
     */
    public function createProduct(Vendor $vendor, array $data, ?UploadedFile $image = null): Product
    {
        $productData = [
            'vendor_id' => $vendor->id,
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::random(6),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? null,
            'is_available' => $data['is_available'] ?? true,
            'status' => $data['status'] ?? 'active',
        ];

        if ($image) {
            $productData['image'] = $image->store('vendors/products', 'public');
        }

        return DB::transaction(function () use ($vendor, $productData, $data) {
            $product = $vendor->products()->create($productData);

            if (! empty($data['addon_name']) && isset($data['addon_price'])) {
                $product->addons()->create([
                    'name' => $data['addon_name'],
                    'price' => $data['addon_price'],
                    'is_available' => true,
                ]);
            }

            if (! empty($data['addons']) && is_array($data['addons'])) {
                foreach ($data['addons'] as $addon) {
                    if (! empty($addon['name']) && isset($addon['price'])) {
                        $product->addons()->create([
                            'name' => $addon['name'],
                            'price' => $addon['price'],
                            'is_available' => $addon['is_available'] ?? true,
                        ]);
                    }
                }
            }

            return $product->fresh(['addons', 'category']);
        });
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        $updateData = [
            'name' => $data['name'] ?? $product->name,
            'category_id' => array_key_exists('category_id', $data) ? $data['category_id'] : $product->category_id,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'discount_price' => array_key_exists('discount_price', $data) ? $data['discount_price'] : $product->discount_price,
            'is_available' => $data['is_available'] ?? $product->is_available,
            'status' => $data['status'] ?? $product->status,
        ];

        if ($image) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $updateData['image'] = $image->store('vendors/products', 'public');
        }

        return DB::transaction(function () use ($product, $updateData, $data) {
            $product->update($updateData);

            if (! empty($data['addon_name']) && isset($data['addon_price'])) {
                $product->addons()->create([
                    'name' => $data['addon_name'],
                    'price' => $data['addon_price'],
                    'is_available' => true,
                ]);
            }

            return $product->fresh(['addons', 'category']);
        });
    }

    /**
     * Delete a product and its image.
     */
    public function deleteProduct(Product $product): bool
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->addons()->delete();
        return (bool) $product->delete();
    }
}
