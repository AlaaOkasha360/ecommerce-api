<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriesResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        return CategoriesResource::collection(Category::all());
    }

    public function show(Request $request, $category_id)
    {
        $category = Category::find($category_id);
        return response()->json([
            'name' => $category->name,
            'products' => $category->products,
        ]);
    }
}
