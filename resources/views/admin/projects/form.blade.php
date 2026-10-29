@extends('admin.layouts.app')
@php($editing = (bool) $project)
@section('title', $editing ? 'แก้ไขโครงการ' : 'เพิ่มโครงการ')
@section('heading', $editing ? 'แก้ไขโครงการ' : 'เพิ่มโครงการใหม่')
@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $editing ? route('admin.projects.update', [$type, $project->id]) : route('admin.projects.store', $type) }}">
        @csrf @if ($editing)
            @method('PUT')
        @endif
        <div class="card card-outline card-teal">
            <div class="card-header">
                <h3 class="card-title">{{ strtoupper($type) }} · ข้อมูล 3 ภาษา</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">กรุณาตรวจสอบข้อมูลที่กรอก</div>
                @endif
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group"><label>ชื่อโครงการ (ไทย) *</label><input name="title_th"
                                class="form-control" required value="{{ old('title_th', $project->title_th ?? '') }}"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>ลำดับการแสดงผล</label><input type="number" min="1"
                                name="sort_order" class="form-control"
                                value="{{ old('sort_order', $project->sort_order ?? '') }}"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Project title (English)</label><input name="title_en"
                                class="form-control" value="{{ old('title_en', $project->title_en ?? '') }}"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>项目名称（中文）</label><input name="title_zh" class="form-control"
                                value="{{ old('title_zh', $project->title_zh ?? '') }}"></div>
                    </div>
                </div>
                <div class="form-group"><label>รหัสอ้างอิง</label><input name="legacy_key" class="form-control"
                        value="{{ old('legacy_key', $project->legacy_key ?? '') }}"></div>
                @foreach (['scale' => $type === 'ppa' ? 'มูลค่าโครงการ' : 'ขนาด/กำลังผลิต', 'configuration' => 'รูปแบบโครงการ', 'location' => 'สถานที่', 'model' => 'รูปแบบธุรกิจ'] as $field => $label)
                    <div class="form-group"><label class="text-teal">{{ $label }}</label>
                        <div class="row">
                            <div class="col-md-4"><input name="{{ $field }}_th" class="form-control"
                                    placeholder="ไทย" value="{{ old($field . '_th', $project->{$field . '_th'} ?? '') }}">
                            </div>
                            <div class="col-md-4"><input name="{{ $field }}_en" class="form-control"
                                    placeholder="English"
                                    value="{{ old($field . '_en', $project->{$field . '_en'} ?? '') }}">
                            </div>
                            <div class="col-md-4"><input name="{{ $field }}_zh" class="form-control"
                                    placeholder="中文" value="{{ old($field . '_zh', $project->{$field . '_zh'} ?? '') }}">
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="form-group"><label class="text-teal">รายละเอียด</label>
                    <div class="row">
                        <div class="col-md-4">
                            <textarea name="overview_th" class="form-control" rows="4" placeholder="ไทย">{{ old('overview_th', $project->overview_th ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <textarea name="overview_en" class="form-control" rows="4" placeholder="English">{{ old('overview_en', $project->overview_en ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <textarea name="overview_zh" class="form-control" rows="4" placeholder="中文">{{ old('overview_zh', $project->overview_zh ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>สถานะ (ไทย)</label><input name="status_th" class="form-control"
                                value="{{ old('status_th', $project->status_th ?? '') }}"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>状态（中文）</label><input name="status_zh" class="form-control"
                                value="{{ old('status_zh', $project->status_zh ?? '') }}"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>รูปภาพหลัก</label><input type="file" accept="image/*"
                                name="image" class="form-control-file"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>เพิ่มรูปภาพประกอบ</label><input type="file" accept="image/*"
                                name="gallery_images[]" multiple class="form-control-file"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer"><a href="{{ route('admin.projects', $type) }}"
                    class="btn btn-default">ยกเลิก</a><button class="btn btn-teal float-right">บันทึกข้อมูล</button></div>
        </div>
    </form>
@endsection
