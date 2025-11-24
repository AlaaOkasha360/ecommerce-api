<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ProductsResource;
use App\Http\Resources\ReviewsResource;
use App\HttpResponses;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Auth;
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
        $products = Product::all();
        return $this->product_paginate($products->paginate(10));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }
        return new ProductsResource($product);

    }

    public function search(Request $request)
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

    public function product_category(string $slug)
    {
        $category = Category::where('slug', $slug)->first();
        return $this->success([
            'name' => $category->name,
            'products' => $category->products,
        ]);
    }

    public function product_reviews(Product $product)
    {
        return ReviewsResource::collection($product->reviews);
    }

    public function store_review(StoreReviewRequest $request, Product $product)
    {
        $validated = $request->validated();
        $review = Review::create([
            'user_id' => Auth::user()->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment']
        ]);
        return new ReviewsResource($review);
    }
}
