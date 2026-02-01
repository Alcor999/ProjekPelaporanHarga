@extends('layouts.admin')

@section('title', 'Master Supplier')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0"><i class="fas fa-truck text-primary"></i> Master Supplier</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-1"></i> Tambah Supplier
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="transition: transform .2s, box-shadow .2s;">
    <div class="card-body">
        <form method="GET" id="supplierSearchForm" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Cari</label>
                <input type="text" name="search" id="supplierSearchInput" class="form-control form-control-sm"
                       placeholder="Nama, telepon, alamat..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-0">Wilayah</label>
                <select name="wilayah_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    @foreach($wilayah as $w)
                    <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                        {{ $w->kabupaten }} - {{ $w->kecamatan }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-0">Status</label>
                <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.supplier.index') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync-alt me-1"></i> Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Wilayah</th>
                        <th>Harga (Komoditas)</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                    <tr>
                        <td>
                            <a href="{{ route('admin.supplier.show', $s) }}" class="text-decoration-none fw-semibold text-dark">
                                {{ $s->name }}
                            </a>
                            @if($s->address)
                            <br><small class="text-muted">{{ Str::limit($s->address, 40) }}</small>
                            @endif
                        </td>
                        <td>{{ $s->phone ?? '-' }}</td>
                        <td>{{ $s->wilayah ? $s->wilayah->kabupaten . ', ' . $s->wilayah->kecamatan : '-' }}</td>
                        <td>
                            @forelse($s->harga->take(2) as $h)
                            <span class="badge bg-light text-dark">{{ $h->komoditas->name }}: Rp {{ number_format($h->harga_per_satuan, 0, ',', '.') }}</span>
                            @empty
                            <span class="text-muted">-</span>
                            @endforelse
                            @if($s->harga->count() > 2)
                            <small class="text-muted">+{{ $s->harga->count()-2 }} lainnya</small>
                            @endif
                        </td>
                        <td>
                            @php $avg = $s->avgRating(); @endphp
                            @if($avg > 0)
                            <span class="text-warning"><i class="fas fa-star"></i></span> {{ number_format($avg, 1) }}
                            <small class="text-muted">({{ $s->ratings_count }})</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($s->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.supplier.show', $s) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier({{ json_encode($s) }})" title="Edit"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $s->id }}" title="Hapus"><i class="fas fa-trash"></i></button>
                            <div class="modal fade" id="deleteModal{{ $s->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Hapus Supplier</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.supplier.destroy', $s) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-0">Hapus supplier <strong>{{ $s->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                                                <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Data harga, jadwal, dan rating akan ikut terhapus.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Hapus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-truck fa-2x mb-2 d-block"></i>Belum ada data supplier</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $suppliers->withQueryString()->links() }}
    </div>
</div>

@include('admin.supplier.form-modal')
@endsection

@push('scripts')
<script>
(function() {
    const form = document.getElementById('supplierSearchForm');
    const input = document.getElementById('supplierSearchInput');
    let debounceTimer;
    if (input && form) {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { form.submit(); }, 400);
        });
    }
})();
function editSupplier(s) {
    document.getElementById('formTitle').textContent = 'Edit Supplier';
    document.getElementById('supplierForm').action = '{{ url("admin/supplier") }}/' + s.id;
    document.getElementById('supplierForm').querySelector('input[name="_method"]').value = 'PUT';
    document.querySelector('[name="name"]').value = s.name || '';
    document.querySelector('[name="phone"]').value = s.phone || '';
    document.querySelector('[name="address"]').value = s.address || '';
    document.querySelector('[name="wilayah_id"]').value = s.wilayah_id || '';
    document.querySelector('[name="description"]').value = s.description || '';
    document.querySelector('[name="is_active"]').checked = s.is_active;
    new bootstrap.Modal(document.getElementById('createModal')).show();
}
document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formTitle').textContent = 'Tambah Supplier';
    document.getElementById('supplierForm').action = '{{ route("admin.supplier.store") }}';
    document.getElementById('supplierForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('supplierForm').reset();
});
document.querySelector('[data-bs-target="#createModal"]').addEventListener('click', function() {
    document.getElementById('formTitle').textContent = 'Tambah Supplier';
    document.getElementById('supplierForm').action = '{{ route("admin.supplier.store") }}';
    document.getElementById('supplierForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('supplierForm').reset();
});
</script>
@endpush
