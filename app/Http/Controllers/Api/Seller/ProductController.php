<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\StoreProductRequest;
use App\Http\Requests\Api\Seller\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $query = $supplier->products()->with(['category', 'variants', 'images']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $products->map(fn ($p) => $this->formatProduct($p)),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $product = Product::with(['category', 'variants', 'images'])
            ->where('supplier_id', $supplier->id)
            ->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatProduct($product),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        DB::beginTransaction();
        try {
            $product = Product::create([
                'supplier_id' => $supplier->id,
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status,
            ]);

            foreach ($request->variants as $variantData) {
                ProductVariant::create([
                    'product_id'      => $product->id,
                    'sku'             => $variantData['sku'],
                    'color'           => $variantData['color'] ?? null,
                    'size'            => $variantData['size'] ?? null,
                    'price'           => $variantData['price'],
                    'wholesale_price' => $variantData['wholesale_price'],
                    'min_quantity'    => $variantData['min_quantity'],
                    'stock'           => $variantData['stock'],
                ]);
            }

            if (!empty($request->images)) {
                foreach ($request->images as $index => $imageData) {
                    if (empty($imageData)) continue;
                    $path = $this->saveBase64Image($imageData, 'products');
                    if ($path) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url'  => $path,
                            'is_main'    => $index === 0,
                        ]);
                    }
                }
            }

            DB::commit();

            $product->load(['category', 'variants', 'images']);

            return response()->json([
                'success' => true,
                'message' => 'Tạo sản phẩm thành công',
                'data'    => $this->formatProduct($product),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tạo sản phẩm thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $product = Product::where('supplier_id', $supplier->id)->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        DB::beginTransaction();
        try {
            $product->update([
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status,
            ]);

            $keptVariantIds = [];
            foreach ($request->variants as $variantData) {
                if (!empty($variantData['id'])) {
                    $variant = ProductVariant::where('id', $variantData['id'])
                        ->where('product_id', $product->id)
                        ->first();

                    if ($variant) {
                        $variant->update([
                            'sku'             => $variantData['sku'],
                            'color'           => $variantData['color'] ?? null,
                            'size'            => $variantData['size'] ?? null,
                            'price'           => $variantData['price'],
                            'wholesale_price' => $variantData['wholesale_price'],
                            'min_quantity'    => $variantData['min_quantity'],
                            'stock'           => $variantData['stock'],
                        ]);
                        $keptVariantIds[] = $variant->id;
                    }
                } else {
                    $variant = ProductVariant::create([
                        'product_id'      => $product->id,
                        'sku'             => $variantData['sku'],
                        'color'           => $variantData['color'] ?? null,
                        'size'            => $variantData['size'] ?? null,
                        'price'           => $variantData['price'],
                        'wholesale_price' => $variantData['wholesale_price'],
                        'min_quantity'    => $variantData['min_quantity'],
                        'stock'           => $variantData['stock'],
                    ]);
                    $keptVariantIds[] = $variant->id;
                }
            }
            $product->variants()->whereNotIn('id', $keptVariantIds)->delete();

            if (!empty($request->images)) {
                $currentCount = $product->images()->count();
                foreach ($request->images as $index => $imageData) {
                    if (empty($imageData)) continue;
                    $path = $this->saveBase64Image($imageData, 'products');
                    if ($path) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url'  => $path,
                            'is_main'    => $currentCount === 0 && $index === 0,
                        ]);
                    }
                }
            }

            DB::commit();

            $product->load(['category', 'variants', 'images']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công',
                'data'    => $this->formatProduct($product),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật sản phẩm thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $product = Product::with('images')->where('supplier_id', $supplier->id)->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm',
        ]);
    }

    public function deleteImage(Request $request, int $imageId): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $image = ProductImage::with('product')->find($imageId);

        if (!$image || $image->product->supplier_id !== $supplier->id) {
            return response()->json(['success' => false, 'message' => 'Ảnh không tồn tại'], 404);
        }

        if (Storage::disk('public')->exists($image->image_url)) {
            Storage::disk('public')->delete($image->image_url);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ảnh',
        ]);
    }

    private function saveBase64Image(string $base64Data, string $directory): ?string
    {
        $imageData = preg_replace('/^data:image\/[^;]+;base64,/', '', $base64Data);
        $decoded   = base64_decode($imageData, true);

        if ($decoded === false || strlen($decoded) > 2 * 1024 * 1024) {
            return null;
        }

        $extension = 'jpg';
        if (str_contains($base64Data, 'data:image/png')) {
            $extension = 'png';
        } elseif (str_contains($base64Data, 'data:image/gif')) {
            $extension = 'gif';
        } elseif (str_contains($base64Data, 'data:image/webp')) {
            $extension = 'webp';
        }

        $filename = Str::uuid() . '.' . $extension;
        $path     = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $decoded);

        return $path;
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id'          => $product->id,
            'name'        => $product->name,
            'description' => $product->description,
            'status'      => $product->status,
            'category'    => $product->relationLoaded('category') ? [
                'id'   => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'variants'    => $product->relationLoaded('variants')
                ? $product->variants->map(fn ($v) => [
                    'id'              => $v->id,
                    'sku'             => $v->sku,
                    'color'           => $v->color,
                    'size'            => $v->size,
                    'price'           => (float) $v->price,
                    'wholesale_price' => (float) $v->wholesale_price,
                    'min_quantity'    => $v->min_quantity,
                    'stock'           => $v->stock,
                ])->values()
                : [],
            'images'      => $product->relationLoaded('images')
                ? $product->images->map(fn ($img) => [
                    'id'       => $img->id,
                    'url'      => $img->image_url,
                    'is_main'  => (bool) $img->is_main,
                ])->values()
                : [],
            'created_at'  => $product->created_at?->toIso8601String(),
            'updated_at'  => $product->updated_at?->toIso8601String(),
        ];
    }
}
