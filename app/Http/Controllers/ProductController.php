<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a paginated listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getAll($request->all());

        return response()->json([
            'message' => 'Products retrieved successfully.',
            'products' => $products,
        ], 200);
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getById($id);

        return response()->json([
            'message' => 'Product retrieved successfully.',
            'product' => $product,
        ], 200);
    }

    /**
     * Display all products belonging to a vendor.
     */
    public function getProductsByVendor(int $vendorId): JsonResponse
    {
        $products = $this->productService->getVendorProducts($vendorId);

        return response()->json([
            'message' => 'Vendor products retrieved successfully.',
            'products' => $products,
        ], 200);
    }

    /**
     * Store a newly created product for the authenticated vendor.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor) {
            return response()->json([
                'message' => 'You do not have a vendor profile. Please create a vendor first before adding products.',
            ], 403);
        }

        $product = $this->productService->createProduct(
            $user->vendor,
            $request->validated(),
            $request->file('image')
        );

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $user = $request->user();

        if (! $user->vendor || ($product->vendor_id !== $user->vendor->id && $user->role !== 'admin')) {
            return response()->json(['message' => 'Unauthorized to update this product.'], 403);
        }

        $updatedProduct = $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->file('image')
        );

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $updatedProduct,
        ], 200);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        if (! $user->vendor || ($product->vendor_id !== $user->vendor->id && $user->role !== 'admin')) {
            return response()->json(['message' => 'Unauthorized to delete this product.'], 403);
        }

        $this->productService->deleteProduct($product);

        return response()->json(['message' => 'Product deleted successfully.'], 200);
    }
}
