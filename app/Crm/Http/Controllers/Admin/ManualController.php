<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManualController extends Controller
{
    private const FILES = [
        'th' => 'crm/manuals/GSB_EV_CRM_Thai.pdf',
        'zh' => 'crm/manuals/GSB_EV_CRM_Chinese.pdf',
    ];

    public function index(Request $request): View
    {
        $language = $request->query('language', app()->getLocale());
        if (! is_string($language) || ! array_key_exists($language, self::FILES)) {
            $language = 'th';
        }

        return view('crm.admin.manuals', [
            'language' => $language,
        ]);
    }

    public function show(string $language, Request $request): BinaryFileResponse
    {
        abort_unless(array_key_exists($language, self::FILES), 404);

        $file = self::FILES[$language];
        abort_unless(Storage::disk('local')->exists($file), 404);

        return response()->file(Storage::disk('local')->path($file), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="'.basename($file).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
