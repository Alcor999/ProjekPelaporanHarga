@extends('layouts.admin')

@section('title', 'Master Wilayah')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0"><i class="fas fa-map-marker-alt text-primary"></i> Master Wilayah</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i> Tambah Wilayah
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" id="wilayahSearchForm" class="mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" id="wilayahSearchInput" class="form-control" placeholder="Cari wilayah (provinsi, kabupaten, kecamatan, desa)..." value="{{ request('search') }}">
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Provinsi</th>
                        <th>Kabupaten</th>
                        <th>Kecamatan</th>
                        <th>Desa</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wilayah as $w)
                    <tr>
                        <td>{{ $w->provinsi }}</td>
                        <td>{{ $w->kabupaten }}</td>
                        <td>{{ $w->kecamatan }}</td>
                        <td>{{ $w->desa ?? '-' }}</td>
                        <td>
                            @if($w->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editWilayah({{ json_encode($w) }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.wilayah.destroy', $w) }}" class="d-inline" onsubmit="return confirm('Hapus wilayah ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data wilayah</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $wilayah->withQueryString()->links() }}
    </div>
</div>

@include('admin.wilayah.form-modal')
@endsection

@push('scripts')
<script>
(function() {
    const form = document.getElementById('wilayahSearchForm');
    const input = document.getElementById('wilayahSearchInput');
    let debounceTimer;
    if (input && form) {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { form.submit(); }, 400);
        });
    }
})();
function editWilayah(w) {
    document.getElementById('formTitle').textContent = 'Edit Wilayah';
    document.getElementById('wilayahForm').action = '/admin/wilayah/' + w.id;
    document.getElementById('wilayahForm').querySelector('input[name="_method"]').value = 'PUT';
    document.querySelector('[name="kecamatan"]').value = w.kecamatan || '';
    document.querySelector('[name="desa"]').value = w.desa || '';
    document.querySelector('[name="kabupaten"]').value = w.kabupaten || '';
    document.querySelector('[name="provinsi"]').value = w.provinsi || '';
    document.querySelector('[name="is_active"]').checked = w.is_active;
    new bootstrap.Modal(document.getElementById('createModal')).show();
}
document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formTitle').textContent = 'Tambah Wilayah';
    document.getElementById('wilayahForm').action = '{{ route("admin.wilayah.store") }}';
    document.getElementById('wilayahForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('wilayahForm').reset();
});
document.querySelector('[data-bs-target="#createModal"]').addEventListener('click', function() {
    document.getElementById('formTitle').textContent = 'Tambah Wilayah';
    document.getElementById('wilayahForm').action = '{{ route("admin.wilayah.store") }}';
    document.getElementById('wilayahForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('wilayahForm').reset();
});
</script>
@endpush
