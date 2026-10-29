<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProjectController extends Controller
{
    private const TYPES = ['ppa', 'epc', 'ev'];

    public function create(string $type): View
    {
        return view('admin.projects.form-translated', ['type' => $type, 'project' => null]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $data = $this->validated($request);
        unset($data['image'], $data['gallery_images'], $data['remove_main_image'], $data['remove_gallery_images']);
        $data['type'] = $type;
        $data['legacy_key'] = $this->uniqueKey($type, $data['legacy_key'] ?: Str::slug($data['title_en'] ?: $data['title_th']));
        $data['image_path'] = $this->storeImage($request, 'image') ?: ($data['image_path'] ?? null);
        $data['gallery_paths'] = json_encode($this->storeGallery($request));
        $data['sort_order'] = (int) ($data['sort_order'] ?: (DB::table('projects')->where('type', $type)->max('sort_order') + 1));
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('projects')->insert($data);

        return redirect()->route('admin.projects', $type)->with('success', __('admin_extra.project_added'));
    }

    public function show(string $type, int $project): View
    {
        return view('admin.projects.show-translated', ['type' => $type, 'project' => $this->project($type, $project)]);
    }

    public function edit(string $type, int $project): View
    {
        return view('admin.projects.form-translated', ['type' => $type, 'project' => $this->project($type, $project)]);
    }

    public function update(Request $request, string $type, int $project): RedirectResponse
    {
        $existing = $this->project($type, $project);
        $data = $this->validated($request);
        $removeImages = $data['remove_images'] ?? [];
        $imageOrder = $data['image_order'] ?? [];
        unset($data['image'], $data['gallery_images'], $data['remove_images'], $data['image_order']);
        $data['legacy_key'] = $this->uniqueKey($type, $data['legacy_key'] ?: $existing->legacy_key, $project);
        $existingGallery = json_decode($existing->gallery_paths ?: '[]', true) ?: [];
        $existingImages = array_values(array_unique(array_filter([$existing->image_path, ...$existingGallery])));
        $removedImages = array_values(array_intersect($existingImages, $removeImages));
        foreach ($removedImages as $path) {
            $this->deleteManagedImage($path);
        }

        $remainingImages = array_values(array_diff($existingImages, $removedImages));
        $orderedImages = $this->orderedImages($imageOrder, $remainingImages);

        if ($path = $this->storeImage($request, 'image')) {
            if ($existing->image_path && ! in_array($existing->image_path, $removedImages, true)) {
                $this->deleteManagedImage($existing->image_path);
            }
            $orderedImages = array_values(array_diff($orderedImages, [$existing->image_path]));
            array_unshift($orderedImages, $path);
        }

        $newGallery = $this->storeGallery($request);
        $allImages = array_values(array_unique(array_merge($orderedImages, $newGallery)));
        $data['image_path'] = array_shift($allImages);
        $data['gallery_paths'] = json_encode($allImages);
        $data['updated_at'] = now();
        DB::table('projects')->where('id', $project)->update($data);

        return redirect()->route('admin.projects.show', [$type, $project])->with('success', __('admin_extra.project_saved'));
    }

    public function destroy(string $type, int $project): RedirectResponse
    {
        $this->project($type, $project);
        DB::table('projects')->where('id', $project)->delete();

        return redirect()->route('admin.projects', $type)->with('success', __('admin_extra.project_deleted'));
    }

    public function togglePublication(string $type, int $project): RedirectResponse
    {
        $record = $this->project($type, $project);
        $isPublished = ! $record->is_published;

        DB::table('projects')->where('id', $project)->update([
            'is_published' => $isPublished,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.projects', $type)->with('success', $isPublished ? 'เผยแพร่โครงการบนเว็บไซต์แล้ว' : 'ซ่อนโครงการจากเว็บไซต์แล้ว');
    }

    private function project(string $type, int $id): object
    {
        abort_unless(in_array($type, self::TYPES, true), 404);

        return DB::table('projects')->where('type', $type)->where('id', $id)->firstOrFail();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'legacy_key' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable', 'boolean'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_th' => ['required', 'string', 'max:255'],
            'title_zh' => ['nullable', 'string', 'max:255'],
            'scale_en' => ['nullable', 'string', 'max:1000'],
            'scale_th' => ['nullable', 'string', 'max:1000'],
            'scale_zh' => ['nullable', 'string', 'max:1000'],
            'configuration_en' => ['nullable', 'string', 'max:1000'],
            'configuration_th' => ['nullable', 'string', 'max:1000'],
            'configuration_zh' => ['nullable', 'string', 'max:1000'],
            'location_en' => ['nullable', 'string', 'max:1000'],
            'location_th' => ['nullable', 'string', 'max:1000'],
            'location_zh' => ['nullable', 'string', 'max:1000'],
            'model_en' => ['nullable', 'string', 'max:1000'],
            'model_th' => ['nullable', 'string', 'max:1000'],
            'model_zh' => ['nullable', 'string', 'max:1000'],
            'overview_en' => ['nullable', 'string', 'max:5000'],
            'overview_th' => ['nullable', 'string', 'max:5000'],
            'overview_zh' => ['nullable', 'string', 'max:5000'],
            'status_th' => ['nullable', 'string', 'max:255'],
            'status_zh' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:5120'],
            'image_order' => ['nullable', 'array'],
            'image_order.*' => ['string', 'max:1000'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string', 'max:1000'],
        ]);
    }

    private function uniqueKey(string $type, string $key, ?int $exceptId = null): string
    {
        $base = Str::slug($key) ?: $type.'-project';
        $candidate = $base;
        $suffix = 2;
        while (DB::table('projects')->where('legacy_key', $candidate)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function storeImage(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }
        $file = $request->file($field);
        $directory = $this->uploadsDirectory();
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $name);

        return 'uploads/projects/'.$name;
    }

    private function storeGallery(Request $request): array
    {
        $paths = [];
        foreach ($request->file('gallery_images', []) as $file) {
            $directory = $this->uploadsDirectory();
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $name = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move($directory, $name);
            $paths[] = 'uploads/projects/'.$name;
        }

        return $paths;
    }

    private function deleteManagedImage(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'uploads/projects/')) {
            return;
        }

        $relativePath = str_replace('\\', '/', $path);
        if (str_contains($relativePath, '..')) {
            return;
        }

        $file = $this->webRoot().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($file)) {
            unlink($file);
        }
    }

    private function orderedImages(array $requestedOrder, array $availableImages): array
    {
        $ordered = array_values(array_intersect($requestedOrder, $availableImages));

        return array_values(array_unique([...$ordered, ...array_diff($availableImages, $ordered)]));
    }

    /**
     * Xinnet serves the deployed Laravel application directly from /www.
     * Locally, Laravel continues to serve public/ as usual.
     */
    private function uploadsDirectory(): string
    {
        return $this->webRoot().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'projects';
    }

    private function webRoot(): string
    {
        return is_file(base_path('index.php')) ? base_path() : public_path();
    }
}
