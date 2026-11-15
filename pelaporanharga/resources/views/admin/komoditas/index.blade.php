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
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit-komoditas"
                                    data-update-url="{{ route('admin.komoditas.update', $k) }}"
                                    data-kategori="{{ $k->kategori ?? 'padi' }}"
                                    data-name="{{ $k->name }}"
                                    data-grade="{{ $k->grade ?? '' }}"
                                    data-description="{{ $k->description ?? '' }}"
                                    data-active="{{ $k->is_active ? '1' : '0' }}"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteKomoditasModal{{ $k->id }}" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            <div class="modal fade" id="deleteKomoditasModal{{ $k->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Hapus Komoditas</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.komoditas.destroy', $k) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-0">Apakah Anda yakin ingin menghapus komoditas <strong>{{ $k->name }} {{ $k->grade ? '('.$k->grade.')' : '' }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                                                <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Pastikan tidak ada laporan yang menggunakan komoditas ini.</p>
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

    const modalEl = document.getElementById('createModal');
    const formEl = document.getElementById('komoditasForm');
    const storeUrl = @json(route('admin.komoditas.store'));

    function setModalState(title, actionUrl, method) {
        document.getElementById('formTitle').textContent = title;
        formEl.action = actionUrl;
        formEl.querySelector('input[name="_method"]').value = method;
        formEl.querySelector('input[name="_form_action"]').value = actionUrl;
        formEl.querySelector('input[name="_form_method"]').value = method;
        formEl.querySelector('input[name="_modal_title"]').value = title;
    }

    function openCreateKomoditas() {
        formEl.reset();
        setModalState('Tambah Komoditas', storeUrl, 'POST');
        formEl.querySelector('[name="kategori"]').value = 'padi';
        formEl.querySelector('[name="is_active"]').checked = true;
    }

    document.querySelectorAll('.btn-edit-komoditas').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setModalState('Edit Komoditas', btn.dataset.updateUrl, 'PUT');
            formEl.querySelector('[name="kategori"]').value = btn.dataset.kategori || 'padi';
            formEl.querySelector('[name="name"]').value = btn.dataset.name || '';
            formEl.querySelector('[name="grade"]').value = btn.dataset.grade || '';
            formEl.querySelector('[name="description"]').value = btn.dataset.description || '';
            formEl.querySelector('[name="is_active"]').checked = btn.dataset.active === '1';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });

    const addTrigger = document.querySelector('[data-bs-target="#createModal"]');
    if (addTrigger) {
        addTrigger.addEventListener('click', openCreateKomoditas);
    }

    modalEl.addEventListener('hidden.bs.modal', function() {
        openCreateKomoditas();
    });

    @if($errors->any())
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif
})();
</script>
@endpush
