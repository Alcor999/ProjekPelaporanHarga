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
                            <td>{{ $user->name }}</td>
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
                                        <form method="POST" action="{{ route('admin.users.petani.approve', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve akun ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($user->status !== 'blocked')
                                        <form method="POST" action="{{ route('admin.users.petani.block', $user) }}" class="d-inline ms-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Blokir akun ini?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
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
