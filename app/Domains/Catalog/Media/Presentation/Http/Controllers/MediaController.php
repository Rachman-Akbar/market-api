<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Media\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class MediaController extends Controller
{
    public function storeImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'scope' => ['nullable', 'string', 'max:120'],
        ]);

        $scope = trim((string) ($validated['scope'] ?? 'general'));
        $scope = trim(Str::slug(str_replace('/', '-', $scope)), '-');
        $scope = $scope !== '' ? $scope : 'general';
        $file = $request->file('image');
        $path = $file->store("marketplace/{$scope}", 'public');
        $publicPath = '/storage/' . ltrim($path, '/');
        $publicUrl = rtrim($request->getSchemeAndHttpHost(), '/') . $publicPath;

        return response()->json([
            'data' => [
                'path' => $path,
                'public_path' => $publicPath,
                'url' => $publicUrl,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ],
        ], 201);
    }
}
