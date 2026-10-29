@extends('admin.layouts.app')
@section('title', 'รีวิวลูกค้า')
@section('heading', 'รีวิวลูกค้า')
@section('content')
    <div class="card"><div class="card-header"><h3 class="card-title">รายการรีวิวที่แสดงบนหน้า Home</h3></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>ผู้รีวิว</th><th>คะแนน</th><th>สถานะ</th><th></th></tr></thead><tbody>@foreach($testimonials as $testimonial)<tr><td>{{ $testimonial->sort_order }}</td><td><strong>{{ $testimonial->name }}</strong><small class="d-block text-muted">{{ $testimonial->company }}</small></td><td class="text-warning">{{ str_repeat('★', $testimonial->rating) }}</td><td><span class="badge badge-{{ $testimonial->is_published ? 'success' : 'secondary' }}">{{ $testimonial->is_published ? 'เผยแพร่' : 'ซ่อน' }}</span></td><td class="text-right"><a class="btn btn-xs btn-warning" href="{{ route('admin.testimonials.edit', $testimonial) }}"><i class="fas fa-pen"></i></a></td></tr>@endforeach</tbody></table></div></div>
@endsection
