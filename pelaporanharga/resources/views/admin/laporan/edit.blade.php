@extends('layouts.admin')

@section('title', 'Edit Laporan')

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.laporan.index') }}">Laporan</a></li>
                <li class="breadcrumb-item active">Edit #{{ $laporan->id }}</li>
            </ol>
        </nav>
        <h1 class="mb-4"><i class="fas fa-edit text-primary"></i> Edit Laporan</h1>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.laporan.update', $laporan) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Komoditas</label>
                    <input type="text" class="form-control" value="{{ $laporan->komoditas->name }} {{ $laporan->komoditas->grade }}" disabled>
                    <small class="text-muted">Tidak dapat diubah</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Wilayah</label>
                    <input type="text" class="form-control" value="{{ $laporan->wilayah->kecamatan }}, {{ $laporan->wilayah->kabupaten }}" disabled>
                    <small class="text-muted">Tidak dapat diubah</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga <span class="text-danger">*</span></label>
                    <input type="number" name="harga" class="form-control @error('harga') is-invalid @enderror"
                           value="{{ old('harga', $laporan->harga) }}" min="1" step="0.01" required>
                    @error('harga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                    <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror"
                           value="{{ old('satuan', $laporan->satuan) }}" maxlength="10" required>
                    @error('satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_panen" class="form-control @error('tanggal_panen') is-invalid @enderror"
                           value="{{ old('tanggal_panen', $laporan->tanggal_panen->format('Y-m-d')) }}" required>
                    @error('tanggal_panen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ old('status', $laporan->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status', $laporan->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status', $laporan->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan', $laporan->catatan) }}</textarea>
                    @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12" id="rejectionReasonBox" style="{{ old('status', $laporan->status) != 'rejected' ? 'display:none' : '' }}">
                    <label class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" id="rejectionReasonInput" class="form-control @error('rejection_reason') is-invalid @enderror" rows="3" placeholder="Wajib diisi jika status Rejected" {{ old('status', $laporan->status) === 'rejected' ? 'required' : '' }}>{{ old('rejection_reason', $laporan->rejection_reason) }}</textarea>
                    @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="{{ route('admin.laporan.show', $laporan) }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const isRejected = this.value === 'rejected';
    document.getElementById('rejectionReasonBox').style.display = isRejected ? 'block' : 'none';
    document.getElementById('rejectionReasonInput').required = isRejected;
});
</script>
@endpush
@endsection
