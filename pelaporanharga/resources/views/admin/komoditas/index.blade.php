@extends('layouts.admin')

@section('title', 'Master Komoditas')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0"><i class="fas fa-seedling text-primary"></i> Master Komoditas</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i> Tambah Komoditas
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" id="komoditasFilterForm" class="row g-2 mb-3 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" id="komoditasSearchInput" class="form-control form-control-sm" placeholder="Cari nama atau grade..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @php $kategoris = \App\Models\Komoditas::distinct()->pluck('kategori'); @endphp
                    @foreach($kategoris as $k)
                    <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ ucfirst($k) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.komoditas.index') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync-alt me-1"></i> Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Nama</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($komoditas as $k)
                    <tr>
                        <td><span class="badge bg-info">{{ $k->kategori ?? 'padi' }}</span></td>
                        <td>{{ $k->name }}</td>
                        <td>{{ $k->grade ?? '-' }}</td>
                        <td>
                            @if($k->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editKomoditas({{ json_encode($k) }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.komoditas.destroy', $k) }}" class="d-inline" onsubmit="return confirm('Hapus komoditas ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data komoditas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $komoditas->withQueryString()->links() }}
    </div>
</div>

@include('admin.komoditas.form-modal')
@endsection

@push('scripts')
<script>
(function() {
    const form = document.getElementById('komoditasFilterForm');
    const input = document.getElementById('komoditasSearchInput');
    let debounceTimer;
    if (input && form) {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { form.submit(); }, 400);
        });
    }
})();
function editKomoditas(k) {
    document.getElementById('formTitle').textContent = 'Edit Komoditas';
    document.getElementById('komoditasForm').action = '/admin/komoditas/' + k.id;
    document.getElementById('komoditasForm').querySelector('input[name="_method"]').value = 'PUT';
    document.querySelector('[name="kategori"]').value = k.kategori || 'padi';
    document.querySelector('[name="name"]').value = k.name || '';
    document.querySelector('[name="grade"]').value = k.grade || '';
    document.querySelector('[name="description"]').value = k.description || '';
    document.querySelector('[name="is_active"]').checked = k.is_active;
    new bootstrap.Modal(document.getElementById('createModal')).show();
}
document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formTitle').textContent = 'Tambah Komoditas';
    document.getElementById('komoditasForm').action = '{{ route("admin.komoditas.store") }}';
    document.getElementById('komoditasForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('komoditasForm').reset();
});
document.querySelector('[data-bs-target="#createModal"]').addEventListener('click', function() {
    document.getElementById('formTitle').textContent = 'Tambah Komoditas';
    document.getElementById('komoditasForm').action = '{{ route("admin.komoditas.store") }}';
    document.getElementById('komoditasForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('komoditasForm').reset();
});
</script>
@endpush
