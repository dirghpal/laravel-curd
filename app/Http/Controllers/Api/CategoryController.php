<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->latest()
            ->get();

        return $this->respondSuccess(
            CategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        $slug = Str::slug($data['name']);
        $originalSlug = $slug;
        $count = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data['slug'] = $slug;

        $category = Category::create($data);

        return $this->respondSuccess(
            new CategoryResource($category),
            'Category created successfully.',
            201
        );
    }

    public function show(Category $category)
    {
        return $this->respondSuccess(
            new CategoryResource($category->loadCount('products')),
            'Category details retrieved successfully.'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

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
            new CategoryResource($category),
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