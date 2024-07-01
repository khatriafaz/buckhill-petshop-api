<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        return ProductResource::make($product->load('category'));
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::query()->create($request->validated());

        return ProductResource::make($product);
    }

    public function update(Product $product, UpdateProductRequest $request)
    {
        $product->update($request->validated());

        return ProductResource::make($product->refresh());
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}
