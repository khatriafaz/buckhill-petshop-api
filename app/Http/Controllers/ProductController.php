<?php

namespace App\Http\Controllers;

use App\Http\Requests\Filters\ProductFilterRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     @OA\Response(response="200", description="List products")
     * )
     */
    public function index(ProductFilterRequest $request)
    {
        $products = Product::query()->with('category')
            ->when($request->get('sort_by'), function (Builder $query) {
                $query->orderBy(request('sort_by.field'), request('sort_by.direction'));
            })
            ->paginate($request->integer('limit', null));

        return ProductResource::collection($products);
    }

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
