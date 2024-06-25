<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
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
