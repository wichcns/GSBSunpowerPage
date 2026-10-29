<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSolutionController extends Controller
{
    public function index(): View
    {
        return view('admin.solutions.index', ['solutions' => Solution::query()->orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.solutions.form', ['solution' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['title_en']);
        $data['image_path'] = $this->storeImage($request);
        $data['sort_order'] = $data['sort_order'] ?: ((int) Solution::query()->max('sort_order') + 1);
        Solution::query()->create($data);

        return redirect()->route('admin.solutions.index')->with('success', __('admin.solution_added'));
    }

    public function edit(Solution $solution): View
    {
        return view('admin.solutions.form', compact('solution'));
    }

    public function update(Request $request, Solution $solution): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $solution->slug, $solution->id);
        if ($imagePath = $this->storeImage($request)) {
            $data['image_path'] = $imagePath;
        }
        $solution->update($data);

        return redirect()->route('admin.solutions.index')->with('success', __('admin.solution_saved'));
    }

    public function destroy(Solution $solution): RedirectResponse
    {
        $solution->delete();

        return redirect()->route('admin.solutions.index')->with('success', __('admin.solution_deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:120'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_th' => ['nullable', 'string', 'max:255'],
            'title_zh' => ['nullable', 'string', 'max:255'],
            'summary_en' => ['nullable', 'string', 'max:5000'],
            'summary_th' => ['nullable', 'string', 'max:5000'],
            'summary_zh' => ['nullable', 'string', 'max:5000'],
            'body_en' => ['nullable', 'string', 'max:20000'],
            'body_th' => ['nullable', 'string', 'max:20000'],
            'body_zh' => ['nullable', 'string', 'max:20000'],
            'destination_url' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]) + ['is_published' => $request->boolean('is_published')];
    }

    private function uniqueSlug(string $value, ?int $exceptId = null): string
    {
        $base = Str::slug($value) ?: 'solution';
        $slug = $base;
        $suffix = 2;
        while (Solution::query()->where('slug', $slug)->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }
        // Xinnet serves the deployed Laravel application directly from /www.
        // Local Laravel installations continue to use the conventional public/ folder.
        $webRoot = is_file(base_path('index.php')) ? base_path() : public_path();
        $directory = $webRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'solutions';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file = $request->file('image');
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $name);

        return 'uploads/solutions/'.$name;
    }
}
