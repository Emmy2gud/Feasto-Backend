<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use User;

class ProductController extends Controller
{
    public function index()
    {
        //display all products buy a vendor
     $vendor =Vendor::find(auth()->user()->vendor->id);
     $products = $vendor->products()->with('productaddons')->paginate(10);
     return response()->json(['products' => $products], 200);

    }
    public function show($id)
    {

    }
    public function store(StoreProductRequest $request)
    {
        //stage 1: validate the request data
        $validatedData = $request->validated();
        //stage 2:check if the authenticated user is a vendor
        if (! auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        if (! $user->vendor()->exists()) {
            return response()->json([
                'message' => 'You do not have a vendor associated with your account. Please create a vendor first before adding products.',
            ], 409);
        }

        //stage3:get product images and other data from the request and prepare it for storage
        $productData = [

            'vendor_id'      => $user->vendor->id,
            'category_id'    => $validatedData['category_id'] ?? null,
            'name'           => $validatedData['name'],
            'description'    => $validatedData['description'] ?? null,
            'price'          => $validatedData['price'],
            'discount_price' => $validatedData['discount_price'] ?? null,
            'is_available'   => $validatedData['is_available'] ?? true,
            'status'         => $validatedData['status'] ?? 'active',

        ];

        if ($request->hasFile('image')) {
            $productData['image'] = $request->file('image')
                ->store('vendors/products', 'public');
        }

        //stage4: create the product and associate it with the vendor

        $product = DB::transaction(function () use ($user, $productData, $validatedData) {
            $vendor = Vendor::find($user->vendor->id);

            // CREATE only — never update here
            $vendor->products()->create($productData);

            // Initial business hours — create once
            $addons = $vendor->productaddons()->create([
                'name'         => $validatedData['addon_name'],
                'price'        => $validatedData['addon_price'],
                'is_available' => $validatedData['is_available'] ?? true,
            ]);

            return $addons;
        });

        return response()->json([
            'message' => 'Product created successfully.',
            'data'    => $product,
        ], 201);
    }
    public function update(UpdateProductRequest $request, $id)
    {
        $product =Product::findOrFail($id);

        //stage 1: validate the request data
        $validatedData = $request->validated();
        //stage 2:check if the authenticated user is a vendor
        if (! auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        if (! $user->vendor()->exists()) {
            return response()->json([
                'message' => 'You do not have a vendor associated with your account. Please create a vendor first before adding products.',
            ], 409);
        }

        //stage3:get product images and other data from the request and prepare it for storage
        $productData = [

            'name'           => $validatedData['name'],
            'description'    => $validatedData['description'] ?? null,
            'price'          => $validatedData['price'],
            'discount_price' => $validatedData['discount_price'] ?? null,
            'is_available'   => $validatedData['is_available'] ?? true,
            'status'         => $validatedData['status'] ?? 'active',

        ];

        if ($request->hasFile('image')) {

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $productData['image'] = $request->file('image')->store('vendors/products', 'public');
        }

        //stage4: create the product and associate it with the vendor


            $vendor = Vendor::find($user->vendor->id);

            // CREATE only — never update here
            $vendor->products()->create($productData);

            // Initial business hours — create once
            $vendor->productaddons()->update([
                'name'         => $validatedData['addon_name'],
                'price'        => $validatedData['addon_price'],
                'is_available' => $validatedData['is_available'] ?? true,
            ]);



        return response()->json([
            'message' => 'Product created successfully.',
            'data'    => $product,
        ], 201);
    }
    public function destroy($id)
    {}

    public function getProductsByVendor($vendorId)
    {
        //stage 1: check if the vendor exists
        //stage 2: retrieve products associated with the vendor
        //stage 3: return the products in a response
    }
}
