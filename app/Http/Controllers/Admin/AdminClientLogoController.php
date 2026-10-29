<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminClientLogoController extends Controller
{
    public function index(): View
    {
        return view('admin.client-logos.index', ['logos' => ClientLogo::query()->orderBy('rail')->orderBy('sort_order')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['nullable', 'string', 'max:120'], 'alt_text' => ['nullable', 'string', 'max:160'], 'rail' => ['required', 'in:left,right'], 'sort_order' => ['nullable', 'integer', 'min:1'], 'image' => ['required', 'image', 'max:5120']]);
        $directory = (is_file(base_path('index.php')) ? base_path() : public_path()).'/uploads/client-logos';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file = $request->file('image');
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $name);
        ClientLogo::query()->create([...$data, 'image_path' => 'uploads/client-logos/'.$name, 'sort_order' => $data['sort_order'] ?: ((int) ClientLogo::query()->where('rail', $data['rail'])->max('sort_order') + 1), 'is_published' => true]);

        return back()->with('success', 'เพิ่มโลโก้เรียบร้อยแล้ว');
    }

    public function destroy(ClientLogo $clientLogo): RedirectResponse
    {
        $clientLogo->delete();

        return back()->with('success', 'ลบโลโก้ออกจากรายการแล้ว');
    }
}
