<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BlogMediaController extends Controller
{
    /**
     * Serve Blog upload images through Laravel so Xinnet does not need a
     * public/storage symbolic link.
     */
    public function show(string $path): BinaryFileResponse
    {
        abort_unless(str_starts_with($path, 'uploads/blog/') && !str_contains($path, '..'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
