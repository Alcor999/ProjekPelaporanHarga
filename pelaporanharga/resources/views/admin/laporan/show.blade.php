@extends('layouts.admin')

@section('title', 'Detail Laporan')

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.laporan.index') }}">Laporan</a></li>
                <li class="breadcrumb-item active">Detail #{{ $laporan->id }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-file-alt text-primary"></i> Detail Laporan</h1>
            <div>
                <a href="{{ route('admin.laporan.edit', $laporan) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Laporan</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Komoditas</label>
                        <p class="mb-0 fw-bold">{{ $laporan->komoditas->name }} {{ $laporan->komoditas->grade ? '('.$laporan->komoditas->grade.')' : '' }}</p>
                        @if($laporan->komoditas->kategori ?? null)
                        <span class="badge bg-info">{{ $laporan->komoditas->kategori }}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Harga</label>
                        <p class="mb-0 fw-bold text-success fs-4">Rp {{ number_format($laporan->harga, 0, ',', '.') }}/{{ $laporan->satuan }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Wilayah</label>
                        <p class="mb-0">{{ $laporan->wilayah->desa ?? '-' }}, {{ $laporan->wilayah->kecamatan }}, {{ $laporan->wilayah->kabupaten }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Tanggal Panen</label>
                        <p class="mb-0">{{ $laporan->tanggal_panen->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Catatan</label>
                        <p class="mb-0">{{ $laporan->catatan ?? '-' }}</p>
                    </div>
                    @if($laporan->rejection_reason)
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> Alasan Ditolak:</strong><br>
                            {{ $laporan->rejection_reason }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> Pelapor</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $laporan->user->name }}</strong></p>
                <p class="mb-1 text-muted small">{{ $laporan->user->email }}</p>
                @if($laporan->user->phone)
                <p class="mb-1 small"><i class="fas fa-phone"></i> {{ $laporan->user->phone }}</p>
                @endif
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-flag"></i> Status</h5>
            </div>
            <div class="card-body text-center">
                @if($laporan->status == 'pending')
                    <span class="badge bg-warning fs-6 px-3 py-2">Pending</span>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                @elseif($laporan->status == 'approved')
                    <span class="badge bg-success fs-6 px-3 py-2">Approved</span>
                @else
                    <span class="badge bg-danger fs-6 px-3 py-2">Rejected</span>
                @endif
            </div>
        </div>
        @if($laporan->foto_path)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-camera"></i> Foto Bukti</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $laporan->foto_path) }}" alt="Foto" class="img-fluid rounded" style="max-height: 200px;">
            </div>
        </div>
        @endif
    </div>
</div>

@if($laporan->status == 'pending')
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check"></i> Approve Laporan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.laporan.approve', $laporan) }}">
                @csrf
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyetujui laporan ini? Petani akan menerima notifikasi.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times"></i> Reject Laporan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.laporan.reject', $laporan) }}">
                @csrf
                <div class="modal-body">
                    <p>Alasan penolakan akan dikirim ke petani. Mohon isi alasan:</p>
                    <div class="mb-3">
                        <label class="form-label">Alasan Reject</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Contoh: Data harga tidak sesuai dengan pasar..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
