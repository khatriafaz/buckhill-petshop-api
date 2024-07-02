<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Files',
    description: 'Files API endpoints'
)]
class FileController extends Controller
{
    #[OA\Post(
        path: '/api/v1/file/upload',
        summary: 'Upload a file',
        tags: ['Files'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'file', description: 'File to upload', type: 'file', format: 'binary')
                ])
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Unprocessed entity'),
        ],
    )]
    public function store(FileUploadRequest $request)
    {
        $file = $request->file('file');
        $path = Storage::disk('pet-shop')->putFileAs('', $file, $file->hashName());

        $file = File::query()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => Storage::disk('pet-shop')->size($path),
            'type' => Storage::disk('pet-shop')->mimeType($path),
        ]);

        return response()->json([
            'data' => [
                'uuid' => $file->uuid,
            ]
        ]);
    }

    public function show(File $file)
    {
        return Storage::disk('pet-shop')->download($file->path, $file->name);
    }
}
