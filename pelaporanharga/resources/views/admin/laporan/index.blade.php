@extends('layouts.admin')

@section('title', 'Kelola Laporan Harga')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-file-alt"></i> Kelola Laporan Harga
            </h1>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter"></i> Filter Laporan
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="wilayah_id" class="form-label">Wilayah</label>
                <select name="wilayah_id" id="wilayah_id" class="form-select">
                    <option value="">Semua Wilayah</option>
                    @foreach($wilayah as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->kabupaten }} - {{ $w->kecamatan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="komoditas_id" class="form-label">Komoditas</label>
                <select name="komoditas_id" id="komoditas_id" class="form-select">
                    <option value="">Semua Komoditas</option>
                    @foreach($komoditas as $k)
                        <option value="{{ $k->id }}" {{ request('komoditas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->name }} {{ $k->grade ? '('.$k->grade.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control"
                       value="{{ request('tanggal_dari') }}">
            </div>

            <div class="col-md-3">
                <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control"
                       value="{{ request('tanggal_sampai') }}">
            </div>

            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Laporan Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-table"></i> Daftar Laporan ({{ $laporan->total() }} total)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Petani</th>
                        <th>Wilayah</th>
                        <th>Komoditas</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $item)
                        <tr>
                            <td>
                                <div>{{ $item->tanggal_laporan->format('d/m/Y') }}</div>
                                <small class="text-muted">Panen: {{ $item->tanggal_panen->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <div>{{ $item->user->name }}</div>
                                <small class="text-muted">{{ $item->user->email }}</small>
                            </td>
                            <td>
                                <div>{{ $item->wilayah->kabupaten }}</div>
                                <small class="text-muted">{{ $item->wilayah->kecamatan }}</small>
                            </td>
                            <td>
                                <div>{{ $item->komoditas->name }}</div>
                                <small class="text-muted">{{ $item->komoditas->grade }}</small>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($item->harga, 0, ',', '.') }}</strong>
                                <small class="text-muted">/{{ $item->satuan }}</small>
                            </td>
                            <td>
                                @if($item->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($item->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.laporan.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.laporan.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($item->status == 'pending')
                                        <form method="POST" action="{{ route('admin.laporan.approve', $item) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Approve laporan ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.laporan.reject', $item) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Reject laporan ini?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <br>
                                Tidak ada laporan ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($laporan->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $laporan->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection