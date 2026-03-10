<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        $query = $supplier->products()->with(['category', 'variants', 'images']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('seller.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('seller.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,hidden'],
            
            // Variants
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.wholesale_price' => ['required', 'numeric', 'min:0'],
            'variants.*.min_quantity' => ['required', 'integer', 'min:1'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            
            // Images
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            // Create product
            $product = Product::create([
                'supplier_id' => $supplier->id,
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            // Create variants
            foreach ($validated['variants'] as $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantData['sku'],
                    'color' => $variantData['color'] ?? null,
                    'size' => $variantData['size'] ?? null,
                    'price' => $variantData['price'],
                    'wholesale_price' => $variantData['wholesale_price'],
                    'min_quantity' => $variantData['min_quantity'],
                    'stock' => $variantData['stock'],
                ]);
            }

            // Upload images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    if ($image->isValid()) {
                        $path = $image->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $path,
                            'is_primary' => $index === 0,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('seller.products.index')->with('success', __('seller.products.created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => __('seller.common.error_occurred') . ' ' . $e->getMessage()]);
        }
    }

    public function edit(Product $product)
    {
        // Check ownership
        if ($product->supplier_id !== Auth::user()->supplier->id) {
            abort(403);
        }

        $categories = Category::whereNull('parent_id')->with('children')->get();
        $product->load(['variants', 'images']);
        
        return view('seller.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // Check ownership
        if ($product->supplier_id !== Auth::user()->supplier->id) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,hidden'],
            
            // Variants
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'exists:product_variants,id'],
            'variants.*.sku' => ['required', 'string', 'max:100'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.wholesale_price' => ['required', 'numeric', 'min:0'],
            'variants.*.min_quantity' => ['required', 'integer', 'min:1'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            
            // Images
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            // Update product
            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            // Update variants
            $existingVariantIds = [];
            foreach ($validated['variants'] as $variantData) {
                if (isset($variantData['id'])) {
                    // Update existing variant
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant && $variant->product_id === $product->id) {
                        $variant->update([
                            'sku' => $variantData['sku'],
                            'color' => $variantData['color'] ?? null,
                            'size' => $variantData['size'] ?? null,
                            'price' => $variantData['price'],
                            'wholesale_price' => $variantData['wholesale_price'],
                            'min_quantity' => $variantData['min_quantity'],
                            'stock' => $variantData['stock'],
                        ]);
                        $existingVariantIds[] = $variant->id;
                    }
                } else {
                    // Create new variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'color' => $variantData['color'] ?? null,
                        'size' => $variantData['size'] ?? null,
                        'price' => $variantData['price'],
                        'wholesale_price' => $variantData['wholesale_price'],
                        'min_quantity' => $variantData['min_quantity'],
                        'stock' => $variantData['stock'],
                    ]);
                    $existingVariantIds[] = $variant->id;
                }
            }

            // Delete removed variants
            $product->variants()->whereNotIn('id', $existingVariantIds)->delete();

            // Upload new images
            if ($request->hasFile('images')) {
                $currentImagesCount = $product->images()->count();
                foreach ($request->file('images') as $index => $image) {
                    if ($image->isValid()) {
                        $path = $image->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $path,
                            'is_primary' => ($currentImagesCount === 0 && $index === 0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('seller.products.index')->with('success', __('seller.products.updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => __('seller.common.error_occurred') . ' ' . $e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        // Check ownership
        if ($product->supplier_id !== Auth::user()->supplier->id) {
            abort(403);
        }

        // Delete images from storage
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
        }

        $product->delete();

        return redirect()->route('seller.products.index')->with('success', __('seller.products.deleted'));
    }

    public function deleteImage(ProductImage $image)
    {
        // Check ownership
        if ($image->product->supplier_id !== Auth::user()->supplier->id) {
            abort(403);
        }

        // Delete from storage
        if (Storage::disk('public')->exists($image->image_url)) {
            Storage::disk('public')->delete($image->image_url);
        }

        $image->delete();

        return back()->with('success', __('seller.products.image_deleted'));
    }
}
