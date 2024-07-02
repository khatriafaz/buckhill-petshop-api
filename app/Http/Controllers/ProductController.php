<?php

namespace App\Http\Controllers;

use App\Http\Requests\Filters\ProductFilterRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Products',
    description: 'Products API endpoints'
)]
class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/v1/products',
        summary: 'List all products',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(
                name: 'sort_by[field]',
                description: 'Sort field',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: 'price'
            ),
            new OA\Parameter(
                name: 'sort_by[direction]',
                description: 'Sort direction',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: 'desc'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 422, description: 'Unprocessed entity')
        ]
    )]
    public function index(ProductFilterRequest $request)
    {
        $products = Product::query()->with('category')
            ->when($request->get('sort_by'), function (Builder $query) {
                $query->orderBy(request('sort_by.field'), request('sort_by.direction'));
            })
            ->paginate($request->integer('limit', null));

        return ProductResource::collection($products);
    }

    #[OA\Get(
        path: '/api/v1/product/{uuid}',
        summary: 'Fetch a product',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', schema: new OA\Schema(type: 'string'), required: true),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Product $product)
    {
        return ProductResource::make($product->load('category'));
    }

    #[OA\Post(
        path: '/api/v1/product/create',
        summary: 'Create a product',
        tags: ['Products'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'category_uuid', example: ''),
                    new OA\Property(property: 'title', example: 'Test product'),
                    new OA\Property(property: 'price', example: 299.99),
                    new OA\Property(property: 'description', example: 'A product description'),
                    new OA\Property(property: 'metadata', properties: [
                        new OA\Property(property: 'brand', example: '')
                    ])
                ]
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Unprocessed entity'),
        ]
    )]
    public function store(StoreProductRequest $request)
    {
        $product = Product::query()->create($request->validated());

        return ProductResource::make($product);
    }

    #[OA\Put(
        path: '/api/v1/product/{uuid}',
        summary: 'Update a product',
        tags: ['Products'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', schema: new OA\Schema(type: 'string'), required: true),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'category_uuid', example: ''),
                    new OA\Property(property: 'title', example: 'Test product'),
                    new OA\Property(property: 'price', example: 299.99),
                    new OA\Property(property: 'description', example: 'A product description'),
                    new OA\Property(property: 'metadata', properties: [
                        new OA\Property(property: 'brand', example: '')
                    ])
                ]
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Unprocessed entity'),
        ]
    )]
    public function update(Product $product, UpdateProductRequest $request)
    {
        $product->update($request->validated());

        return ProductResource::make($product->refresh());
    }

    #[OA\Delete(
        path: '/api/v1/product/{uuid}',
        summary: 'Delete a product',
        tags: ['Products'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', schema: new OA\Schema(type: 'string'), required: true),
        ],
        responses: [
            new OA\Response(response: 204, description: 'No content'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}
