@extends('admin.layouts.app')
@php($editing = (bool) $project)
@section('title', $editing ? __('admin.edit_project') : __('admin.add_new'))
@section('heading', $editing ? __('admin.edit_project') : __('admin.add_new'))
@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $editing ? route('admin.projects.update', [$type, $project->id]) : route('admin.projects.store', $type) }}">
        @csrf @if ($editing)
            @method('PUT')
        @endif
        <div class="card card-outline card-teal admin-project-form-card">
            <div class="card-header">
                <h3 class="card-title">{{ strtoupper($type) }} · {{ __('admin_extra.three_languages') }}</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ __('admin_extra.check_form') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group"><label>{{ __('admin.title_th') }} *</label><input name="title_th"
                                class="form-control" required value="{{ old('title_th', $project->title_th ?? '') }}"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>{{ __('admin.display_order') }}</label><input type="number"
                                min="1" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $project->sort_order ?? '') }}"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>{{ __('admin.title_en') }}</label><input name="title_en"
                                class="form-control" value="{{ old('title_en', $project->title_en ?? '') }}"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>{{ __('admin.title_zh') }}</label><input name="title_zh"
                                class="form-control" value="{{ old('title_zh', $project->title_zh ?? '') }}"></div>
                    </div>
                </div>
                <div class="form-group"><label>{{ __('admin.reference') }}</label><input name="legacy_key"
                        class="form-control" value="{{ old('legacy_key', $project->legacy_key ?? '') }}"></div>
                @foreach (['scale' => $type === 'ppa' ? 'investment_value' : 'capacity', 'configuration' => 'configuration', 'location' => 'location', 'model' => 'business_model'] as $field => $label)
                    <div class="form-group"><label class="text-teal">{{ $field === 'scale' && $type === 'ppa' && app()->getLocale() === 'th' ? 'มูลค่าโครงการ' : __('admin.' . $label) }}</label>
                        @if ($field === 'scale' && $type === 'ppa')
                            <small class="form-text text-muted">กรอกมูลค่าโครงการ เช่น 1,500,000 บาท (โครงการเดิมที่เป็น kW ควรแก้ไขก่อนเผยแพร่)</small>
                        @endif
                        <div class="row">
                            <div class="col-md-4"><input name="{{ $field }}_th" class="form-control"
                                    placeholder="{{ __('admin_extra.thai') }}"
                                    value="{{ old($field . '_th', $project->{$field . '_th'} ?? '') }}"></div>
                            <div class="col-md-4"><input name="{{ $field }}_en" class="form-control"
                                    placeholder="{{ __('admin_extra.english') }}"
                                    value="{{ old($field . '_en', $project->{$field . '_en'} ?? '') }}"></div>
                            <div class="col-md-4"><input name="{{ $field }}_zh" class="form-control"
                                    placeholder="{{ __('admin_extra.chinese') }}"
                                    value="{{ old($field . '_zh', $project->{$field . '_zh'} ?? '') }}"></div>
                        </div>
                    </div>
                @endforeach
                <div class="form-group"><label class="text-teal">{{ __('admin.details') }}</label>
                    <div class="row">
                        <div class="col-md-4">
                            <textarea name="overview_th" class="form-control" rows="4" placeholder="{{ __('admin_extra.thai') }}">{{ old('overview_th', $project->overview_th ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <textarea name="overview_en" class="form-control" rows="4" placeholder="{{ __('admin_extra.english') }}">{{ old('overview_en', $project->overview_en ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <textarea name="overview_zh" class="form-control" rows="4" placeholder="{{ __('admin_extra.chinese') }}">{{ old('overview_zh', $project->overview_zh ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>{{ __('admin.status') }}
                                ({{ __('admin_extra.thai') }})</label><input name="status_th" class="form-control"
                                value="{{ old('status_th', $project->status_th ?? '') }}"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>{{ __('admin.status') }}
                                ({{ __('admin_extra.chinese') }})</label><input name="status_zh" class="form-control"
                                value="{{ old('status_zh', $project->status_zh ?? '') }}"></div>
                    </div>
                </div>
                @php($galleryPaths = $editing ? (json_decode($project->gallery_paths ?: '[]', true) ?: []) : [])
                @php($projectImages = $editing ? array_values(array_unique(array_filter([$project->image_path, ...$galleryPaths]))) : [])
                <section class="admin-media-manager">
                    <div class="admin-media-manager__heading"><span><i class="fas fa-images"></i></span>
                        <div>
                            <h4>จัดการรูปภาพโครงการ</h4>
                            <p>ลากรูปเพื่อจัดลำดับ — รูปที่ 1 จะใช้เป็นรูปหลักของโครงการ</p>
                        </div>
                    </div>
                    @if ($editing && count($projectImages))
                        <div class="admin-media-grid" data-project-media-grid>
                            @foreach ($projectImages as $path)
                                <div class="admin-media-card" draggable="true" data-project-media-card>
                                    <button type="button" class="admin-media-card__drag" title="ลากเพื่อเปลี่ยนลำดับ"
                                        aria-label="ลากเพื่อเปลี่ยนลำดับ"><i class="fas fa-grip-lines"></i></button>
                                    <img src="{{ asset($path) }}" alt="รูปภาพโครงการลำดับ {{ $loop->iteration }}">
                                    <span class="admin-media-card__order">{{ $loop->iteration }}</span>
                                    <span class="admin-media-card__tag">{{ $loop->first ? __('admin.main_image') : __('admin.gallery') }}</span>
                                    <label class="admin-media-card__remove"><input type="checkbox" name="remove_images[]"
                                            value="{{ $path }}"> ลบรูปนี้</label>
                                    <input type="hidden" name="image_order[]" value="{{ $path }}">
                                </div>
                            @endforeach
                        </div>
                    @elseif ($editing)
                        <div class="admin-media-empty"><i class="far fa-image"></i> ยังไม่มีรูปภาพสำหรับโครงการนี้</div>
                    @endif
                    <div class="row mt-3">
                        <div class="col-md-5">
                            <div class="form-group mb-md-0"><label>{{ __('admin.main_image') }}</label>
                                <div class="admin-upload-control"><i class="fas fa-image"></i><span>เลือกรูปหลัก
                                        หรือเปลี่ยนรูปเดิม<small>รองรับไฟล์ภาพ ขนาดไม่เกิน 5 MB</small></span><input
                                        type="file" accept="image/*" name="image" class="form-control-file"></div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0"><label>{{ __('admin.gallery') }}</label>
                                <div class="admin-upload-control"><i
                                        class="fas fa-images"></i><span>เพิ่มรูปภาพประกอบหลายรูปได้พร้อมกัน<small>เลือกหลายไฟล์ได้
                                            · ไม่เกิน 5 MB ต่อรูป</small></span><input type="file" accept="image/*"
                                        name="gallery_images[]" multiple class="form-control-file"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="card-footer"><a href="{{ route('admin.projects', $type) }}"
                    class="btn btn-default">{{ __('admin.cancel') }}</a><button
                    class="btn btn-teal float-right">{{ __('admin.save') }}</button></div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (() => {
            const grid = document.querySelector('[data-project-media-grid]');
            if (!grid) return;

            let draggedCard = null;
            const updateOrder = () => {
                [...grid.querySelectorAll('[data-project-media-card]')].forEach((card, index) => {
                    card.querySelector('.admin-media-card__order').textContent = index + 1;
                    card.querySelector('.admin-media-card__tag').textContent = index === 0 ? '{{ __('admin.main_image') }}' : '{{ __('admin.gallery') }}';
                });
            };

            grid.querySelectorAll('[data-project-media-card]').forEach((card) => {
                card.addEventListener('dragstart', (event) => {
                    draggedCard = card;
                    card.classList.add('is-dragging');
                    event.dataTransfer.effectAllowed = 'move';
                });
                card.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging');
                    draggedCard = null;
                    updateOrder();
                });
                card.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    if (!draggedCard || draggedCard === card) return;
                    const { left, width } = card.getBoundingClientRect();
                    grid.insertBefore(draggedCard, event.clientX < left + width / 2 ? card : card.nextSibling);
                });
            });
        })();
    </script>
@endpush
