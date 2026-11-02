<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\StoreCrmAttachmentRequest;
use App\Crm\Models\CrmAttachment;
use App\Crm\Support\CurrentOpportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class PipelineAttachmentController extends Controller
{
    public function store(string $sourceId, StoreCrmAttachmentRequest $request): JsonResponse
    {
        $this->assertCurrentLead($sourceId);
        $file = $request->file('file');
        if (! $this->allowedContent($file->getRealPath(), strtolower($file->getClientOriginalExtension()))) {
            throw ValidationException::withMessages(['file' => 'เนื้อหาไฟล์ไม่ตรงกับประเภทที่อนุญาต']);
        }
        $path = 'crm/'.hash('sha256', $sourceId).'/'.Str::uuid().'.'.$file->extension();
        abort_unless(Storage::disk('local')->put($path, file_get_contents($file->getRealPath())), 500);

        try {
            $attachment = CrmAttachment::query()->create([
                'lead_source_id' => $sourceId,
                'category' => $request->validated('category'),
                'original_name' => Str::limit($file->getClientOriginalName(), 240, ''),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'note' => $request->validated('note'),
                'uploaded_by_user_id' => $request->user()->id,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return response()->json(['file' => $attachment], 201);
    }

    public function show(string $sourceId, CrmAttachment $attachment, Request $request): BinaryFileResponse
    {
        abort_unless($attachment->lead_source_id === $sourceId, 404);
        abort_unless(Storage::disk('local')->exists($attachment->storage_path), 404);
        $download = $request->boolean('download') || ! str_starts_with($attachment->mime_type, 'image/');

        return response()->file(Storage::disk('local')->path($attachment->storage_path), [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.preg_replace('/[^A-Za-z0-9._-]/', '_', $attachment->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function assertCurrentLead(string $sourceId): void
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        abort_if(CurrentOpportunity::record($sourceId) === null, 404);
    }

    private function allowedContent(string $path, string $extension): bool
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $simple = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'heic' => ['image/heic', 'image/heif'],
            'heif' => ['image/heic', 'image/heif'],
            'pdf' => ['application/pdf'],
        ];
        if (isset($simple[$extension])) {
            return in_array($mime, $simple[$extension], true);
        }
        if (in_array($extension, ['doc', 'xls', 'ppt'], true)) {
            return bin2hex((string) file_get_contents($path, false, null, 0, 8)) === 'd0cf11e0a1b11ae1';
        }
        $officeEntry = ['docx' => 'word/document.xml', 'xlsx' => 'xl/workbook.xml', 'pptx' => 'ppt/presentation.xml'];
        if (! isset($officeEntry[$extension]) || ! class_exists(ZipArchive::class) || ! in_array($mime, ['application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], true)) {
            return false;
        }
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return false;
        }
        $valid = $zip->locateName('[Content_Types].xml') !== false && $zip->locateName($officeEntry[$extension]) !== false;
        $zip->close();

        return $valid;
    }
}
