<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends ApiController
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);
        $sortBy = in_array($request->query('sort_by'), ['name', 'price', 'stock', 'created_at'], true)
            ? $request->query('sort_by') : 'id';
        $sortDirection = $request->query('sort_direction') === 'asc' ? 'asc' : 'desc';

        $products = Product::query()
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->query('search').'%')
                    ->orWhere('description', 'like', '%'.$request->query('search').'%');
            }))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->query('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->query('max_price')))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondSuccess(
            $products->items(),
            'Products retrieved successfully.',
            200,
            [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ]
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateApi($request, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'image' => '0nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product = Product::create($data);

        return $this->respondSuccess($product, 'Product created successfully.', 201);
    }

    public function show(Product $product)
    {
        return $this->respondSuccess($product, 'Product details retrieved successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateApi($request, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product->update($data);

        return $this->respondSuccess($product, 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return $this->respondSuccess(null, 'Product deleted successfully.', 204);
    }
}
