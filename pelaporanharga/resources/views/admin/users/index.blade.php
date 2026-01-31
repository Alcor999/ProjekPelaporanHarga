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

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.petani.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="col-md-5">
                <label for="q" class="form-label">Cari (nama / email)</label>
                <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="Ketik nama atau email">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary me-2" type="submit"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('admin.users.petani.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-table"></i> Daftar User Petani ({{ $petani->total() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
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
