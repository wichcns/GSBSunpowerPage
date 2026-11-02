<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AboutChartMediaController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $path = str_replace('\\', '/', $path);
        abort_unless(preg_match('#^(?:Uploads|uploads)/about/[A-Za-z0-9._-]+$#', $path), 404);

        $webRoot = is_file(base_path('index.php')) ? base_path() : public_path();
        $file = $webRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
        abort_unless(is_file($file), 404);

        return response()->file($file, [
            'Content-Type' => mime_content_type($file) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
