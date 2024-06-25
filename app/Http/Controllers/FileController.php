<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function store(Request $request)
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
}
