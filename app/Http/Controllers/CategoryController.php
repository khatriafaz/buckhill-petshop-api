<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Categories',
    description: 'Categories API endpoints'
)]
class CategoryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/categories',
        summary: 'List all categories',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(
                name: 'sort_by[field]',
                description: 'Sort field',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: 'title'
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
    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->get('sort_by'), function (Builder $query) {
                $query->orderBy(request('sort_by.field'), request('sort_by.direction'));
            })
            ->paginate($request->integer('limit', null));

        return CategoryResource::collection($categories);
    }

    #[OA\Get(
        path: '/api/v1/category/{uuid}',
        summary: 'Fetch a category',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', schema: new OA\Schema(type: 'string'), required: true),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Category $category)
    {
        return CategoryResource::make($category);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::query()->create($request->validated());

        return CategoryResource::make($category);
    }

    public function update(Category $category, StoreCategoryRequest $request)
    {
        $category->update($request->validated());

        return CategoryResource::make($category->fresh());
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
