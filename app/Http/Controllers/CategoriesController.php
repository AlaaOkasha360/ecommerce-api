<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoriesResource;
use App\HttpResponses;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return CategoriesResource::collection(Category::all());
    }

    public function show(Category $category)
    {
        return new CategoriesResource($category);
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $category = Category::create($validated);
        return new CategoriesResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
        $category->update($validated);
        return new CategoriesResource($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
