<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductsResource;
use App\HttpResponses;
use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::query();
        $search_term = $request->query('q', '');
        if (!empty($search_term)) {
            $products->where(function ($query) use ($search_term) {
                $query->where('name', 'LIKE', "%$search_term%")
                    ->orWhere('description', 'LIKE', "%$search_term%");
            });
        }
        return $this->product_paginate($products->paginate(10));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }
        return new ProductsResource($product);

    }

    public function product_category($category_id)
    {
        $category = Category::find($category_id);
        return response()->json([
            'name' => $category->name,
            'products' => $category->products,
        ]);
    }
}
