@extends('layouts.admin')

@section('title', 'User Petani')

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-users"></i> User Petani
        </h1>
    </div>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.users.petani.index') }}" id="userFilterForm" class="row g-3 align-items-end">
            <div class="col-md-2 col-6">
                <label for="status" class="form-label small text-muted mb-0">Status</label>
                <select name="status" id="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="col-md-4 col-12">
                <label for="userSearchInput" class="form-label small text-muted mb-0">Cari nama / email</label>
                <input type="text" name="q" id="userSearchInput" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Ketik untuk mencari...">
            </div>
            <div class="col-md-2 col-6">
                <a href="{{ route('admin.users.petani.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function() {
    const form = document.getElementById('userFilterForm');
    const input = document.getElementById('userSearchInput');
    let debounceTimer;
    if (input && form) {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { form.submit(); }, 400);
        });
    }
})();
</script>
@endpush

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0"><i class="fas fa-table"></i> Daftar User Petani ({{ $petani->total() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($petani as $user)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#viewUserModal{{ $user->id }}">
                                    {{ $user->name }}
                                </button>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->address }}</td>
                            <td>
                                @if($user->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Blocked</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($user->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $user->id }}" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    @if($user->status !== 'blocked')
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#blockModal{{ $user->id }}" title="Blokir">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                                @if($user->status === 'pending')
                                <div class="modal fade" id="approveModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title"><i class="fas fa-check me-2"></i>Approve Petani</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.users.petani.approve', $user) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <p class="mb-0">Aktifkan akun <strong>{{ $user->name }}</strong> ({{ $user->email }})? Petani akan dapat login dan melaporkan harga.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Approve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($user->status !== 'blocked')
                                <div class="modal fade" id="blockModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Blokir Petani</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.users.petani.block', $user) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <p class="mb-0">Blokir akun <strong>{{ $user->name }}</strong> ({{ $user->email }})? Petani tidak akan dapat login.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i>Blokir</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <!-- View User Modal -->
                                <div class="modal fade" id="viewUserModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Detail Petani</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Nama:</strong> {{ $user->name }}</p>
                                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                                <p><strong>Telepon:</strong> {{ $user->phone ?? '-' }}</p>
                                                <p><strong>Alamat:</strong> {{ $user->address ?? '-' }}</p>
                                                <p><strong>Status:</strong> @if($user->status==='pending')<span class="badge bg-warning">Pending</span>@elseif($user->status==='active')<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Blocked</span>@endif</p>
                                            </div>
                                            <div class="modal-footer">
                                                @if($user->status === 'pending')
                                                <form method="POST" action="{{ route('admin.users.petani.approve', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Approve</button>
                                                </form>
                                                @endif
                                                @if($user->status !== 'blocked')
                                                <form method="POST" action="{{ route('admin.users.petani.block', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-ban me-1"></i>Blokir</button>
                                                </form>
                                                @endif
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Belum ada data petani.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($petani->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $petani->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
