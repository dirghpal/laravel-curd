<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->latest()
            ->get();

        return $this->respondSuccess(
            $categories,
            'Categories retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateApi($request, [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $originalSlug = $data['slug'];
        $count = 1;

        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        $category = Category::create($data);

        return $this->respondSuccess(
            $category,
            'Category created successfully.',
            201
        );
    }

    public function show(Category $category)
    {
        return $this->respondSuccess(
            $category->load('products'),
            'Category retrieved successfully.'
        );
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateApi($request, [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        if ($category->name !== $data['name']) {

            $slug = Str::slug($data['name']);
            $originalSlug = $slug;
            $count = 1;

            while (
                Category::where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()
            ) {
                $slug = $originalSlug . '-' . $count++;
            }

            $data['slug'] = $slug;
        }

        $category->update($data);

        return $this->respondSuccess(
            $category,
            'Category updated successfully.'
        );
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return $this->respondSuccess(
            null,
            'Category deleted successfully.'
        );
    }
}