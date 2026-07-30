<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ProductController extends ApiController
{
    #[OA\Get(
        path: "/products",  
        summary: "Get Products",
        tags: ["Products"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Products retrieved successfully"
            )
        ]
    )]
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $sortBy = in_array(
            $request->query('sort_by'),
            ['name', 'price', 'stock', 'created_at'],
            true
        ) ? $request->query('sort_by') : 'id';

        $sortDirection = $request->query('sort_direction') === 'asc'
            ? 'asc'
            : 'desc';

        $products = Product::with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                          ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('min_price'), function ($query) use ($request) {
                $query->where('price', '>=', $request->min_price);
            })
            ->when($request->filled('max_price'), function ($query) use ($request) {
                $query->where('price', '<=', $request->max_price);
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondSuccess(
            ProductResource::collection($products->items()),
            'Products retrieved successfully.',
            200,
            [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ]
        );
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);

        $product = Product::create($data);

        return $this->respondSuccess(
            new ProductResource($product->load('category')),
            'Product created successfully.',
            201
        );
    }

    public function show(Product $product)
    {
        return $this->respondSuccess(
            new ProductResource($product->load('category')),
            'Product details retrieved successfully.'
        );
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            if (
                $product->image_path &&
                Storage::disk('public')->exists($product->image_path)
            ) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);

        $product->update($data);

        return $this->respondSuccess(
            new ProductResource($product->load('category')),
            'Product updated successfully.'
        );
    }

    public function destroy(Product $product)
    {
        if (
            $product->image_path &&
            Storage::disk('public')->exists($product->image_path)
        ) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return $this->respondSuccess(
            null,
            'Product deleted successfully.'
        );
    }
}