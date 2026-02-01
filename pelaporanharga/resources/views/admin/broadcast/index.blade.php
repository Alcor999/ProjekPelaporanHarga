@extends('layouts.admin')

@section('title', 'Broadcast Pesan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-bullhorn text-primary"></i> Broadcast Pesan</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                <i class="fas fa-plus me-1"></i> Kirim Broadcast
            </button>
        </div>
    </div>
</div>

<!-- Modal Kirim Broadcast -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>Kirim Broadcast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.broadcast.store') }}">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Judul broadcast">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Pesan <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="Tulis pesan untuk semua petani...">{{ old('message') }}</textarea>
                        <small class="text-muted">Pesan akan dikirim ke semua petani aktif</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Kirim ke Semua Petani</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <p class="text-muted">Pesan broadcast akan dikirim ke semua petani yang akunnya aktif.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Pesan</th>
                        <th>Dikirim oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($broadcasts as $b)
                    <tr>
                        <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <button type="button" class="btn btn-link p-0 text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#viewBroadcastModal{{ $b->id }}">
                                <strong>{{ $b->title }}</strong>
                            </button>
                        </td>
                        <td>{{ Str::limit($b->message, 80) }}</td>
                        <td>{{ $b->creator->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada broadcast</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($broadcasts as $b)
        <div class="modal fade" id="viewBroadcastModal{{ $b->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">{{ $b->title }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">{{ $b->message }}</p>
                        <hr>
                        <small class="text-muted">Dikirim oleh {{ $b->creator->name ?? '-' }} pada {{ $b->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        {{ $broadcasts->links() }}
    </div>
</div>

@if($errors->any())
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('broadcastModal')).show(); });</script>
@endpush
@endif
@endsection
