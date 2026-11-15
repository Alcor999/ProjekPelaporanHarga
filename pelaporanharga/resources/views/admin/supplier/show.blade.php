@extends('layouts.admin')

@section('title', 'Detail Supplier - ' . $supplier->name)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.supplier.index') }}">Supplier</a></li>
        <li class="breadcrumb-item active">{{ $supplier->name }}</li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0"><i class="fas fa-truck text-primary me-2"></i>{{ $supplier->name }}</h1>
        <div>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSupplierModal">
                <i class="fas fa-edit me-1"></i> Edit Supplier
            </button>
        </div>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100" style="transition: transform .2s, box-shadow .2s;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h5>
            </div>
            <div class="card-body">
                <p><strong>Telepon:</strong> {{ $supplier->phone ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $supplier->address ?? '-' }}</p>
                <p><strong>Wilayah:</strong> {{ $supplier->wilayah ? $supplier->wilayah->kabupaten . ', ' . $supplier->wilayah->kecamatan : '-' }}</p>
                <p><strong>Status:</strong> @if($supplier->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</p>
                @if($supplier->description)
                <p><strong>Deskripsi:</strong><br>{{ $supplier->description }}</p>
                @endif
                @php
                    $avgSupplierRating = $supplier->ratings->avg('rating') ?? 0;
                    $ratingsTotal = $supplier->ratings->count();
                @endphp
                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="text-warning"><i class="fas fa-star"></i></span>
                    <strong>{{ number_format($avgSupplierRating, 1) }}</strong>
                    <span class="text-muted">({{ $ratingsTotal }} ulasan)</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Harga per Komoditas</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHargaModal">
                    <i class="fas fa-plus me-1"></i> Tambah Harga
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Komoditas</th><th>Harga</th><th>Satuan</th><th width="80">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($supplier->harga as $h)
                            <tr>
                                <td>{{ $h->komoditas->name }} {{ $h->komoditas->grade ? '('.$h->komoditas->grade.')' : '' }}</td>
                                <td class="fw-bold text-success">Rp {{ number_format($h->harga_per_satuan, 0, ',', '.') }}</td>
                                <td>/{{ $h->satuan }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteHargaModal{{ $h->id }}" title="Hapus"><i class="fas fa-trash"></i></button>
                                    <div class="modal fade" id="deleteHargaModal{{ $h->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white py-2">
                                                    <h6 class="modal-title">Hapus Harga?</h6>
                                                    <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.supplier.harga.destroy', [$supplier, $h]) }}">
                                                    @csrf @method('DELETE')
                                                    <div class="modal-body py-2">
                                                        <p class="mb-0 small">Hapus harga {{ $h->komoditas->name }}?</p>
                                                    </div>
                                                    <div class="modal-footer py-2">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada harga. Klik "Tambah Harga" untuk menambah.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Jadwal Operasional</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addJadwalModal">
                    <i class="fas fa-plus me-1"></i> Tambah Jadwal
                </button>
            </div>
            <div class="card-body">
                @php $namaHari = \App\Models\SupplierJadwal::namaHari(); @endphp
                <div class="row g-2">
                    @foreach([1,2,3,4,5,6,7] as $hari)
                    @php $j = $supplier->jadwal->firstWhere('hari', $hari); @endphp
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center p-3 rounded {{ $j ? ($j->is_buka ? 'bg-success bg-opacity-10' : 'bg-secondary bg-opacity-10') : 'bg-light' }}">
                            <strong>{{ $namaHari[$hari] ?? 'Hari '.$hari }}</strong>
                            <span>
                                @if($j)
                                @if($j->is_buka)
                                {{ substr($j->jam_buka ?? '', 0, 5) }} - {{ substr($j->jam_tutup ?? '', 0, 5) }}
                                @else
                                <span class="text-muted">Tutup</span>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#deleteJadwalModal{{ $j->id }}"><i class="fas fa-trash"></i></button>
                                <div class="modal fade" id="deleteJadwalModal{{ $j->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white py-2">
                                                <h6 class="modal-title">Hapus Jadwal?</h6>
                                                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.supplier.jadwal.destroy', [$supplier, $j]) }}">
                                                @csrf @method('DELETE')
                                                <div class="modal-body py-2">
                                                    <p class="mb-0 small">Hapus jadwal {{ $j->nama_hari }}?</p>
                                                </div>
                                                <div class="modal-footer py-2">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-1" data-bs-toggle="modal" data-bs-target="#addJadwalModal" onclick="setHari({{ $hari }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Rating & Ulasan Petani</h5>
            </div>
            <div class="card-body">
                @forelse($supplier->ratings->take(10) as $r)
                <div class="border-bottom py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $r->user->name }}</strong>
                        <span class="text-warning">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5-$r->rating) }}</span>
                    </div>
                    @if($r->ulasan)
                    <p class="mb-0 mt-1 text-muted">{{ $r->ulasan }}</p>
                    @endif
                    <small class="text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</small>
                </div>
                @empty
                <p class="text-muted mb-0">Belum ada rating dari petani.</p>
                @endforelse
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Riwayat Penyetoran</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead><tr><th>Tanggal</th><th>Petani</th><th>Komoditas</th><th>Jumlah</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($supplier->penyetoran->take(15) as $p)
                            <tr>
                                <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $p->user->name }}</td>
                                <td>{{ $p->komoditas->name }}</td>
                                <td>{{ number_format($p->jumlah, 0) }} {{ $p->satuan }}</td>
                                <td>Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                                <td><span class="badge {{ $p->status=='completed' ? 'bg-success' : ($p->status=='pending' ? 'bg-warning' : 'bg-secondary') }}">{{ ucfirst($p->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada penyetoran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.supplier.update', $supplier) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wilayah</label>
                        <select name="wilayah_id" class="form-select">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($wilayah as $w)
                            <option value="{{ $w->id }}" {{ old('wilayah_id', $supplier->wilayah_id) == $w->id ? 'selected' : '' }}>{{ $w->kabupaten }} - {{ $w->kecamatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $supplier->description) }}</textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $supplier->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Harga -->
<div class="modal fade" id="addHargaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Tambah/Edit Harga</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.supplier.harga.store', $supplier) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Komoditas *</label>
                        <select name="komoditas_id" class="form-select" required>
                            <option value="">-- Pilih Komoditas --</option>
                            @foreach($komoditas as $k)
                            <option value="{{ $k->id }}">{{ $k->name }} {{ $k->grade ? '('.$k->grade.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga per Satuan (Rp) *</label>
                        <input type="number" name="harga_per_satuan" class="form-control" min="1" step="0.01" required placeholder="0">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" value="kg" placeholder="kg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="addJadwalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Tambah/Edit Jadwal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.supplier.jadwal.store', $supplier) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Hari *</label>
                        <select name="hari" id="jadwalHari" class="form-select" required>
                            @foreach(\App\Models\SupplierJadwal::namaHari() as $val => $nama)
                            <option value="{{ $val }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_buka" id="jadwalIsBuka" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="jadwalIsBuka">Buka hari ini</label>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jam Buka</label>
                            <input type="time" name="jam_buka" class="form-control" value="08:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Tutup</label>
                            <input type="time" name="jam_tutup" class="form-control" value="17:00">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="catatan" class="form-control" placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setHari(h) { document.getElementById('jadwalHari').value = h; }
</script>
@endpush
