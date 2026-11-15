@extends('layouts.admin')

@section('title', 'Penyetoran Petani')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><i class="fas fa-hand-holding-usd text-primary"></i> Penyetoran Petani</h1>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="transition: transform .2s;">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.penyetoran.index') }}" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-0">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Supplier::active()->orderBy('name')->get() as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-0">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-0">Dari</label>
                <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-0">Sampai</label>
                <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2 col-6">
                <a href="{{ route('admin.penyetoran.index') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync-alt me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0" style="transition: transform .2s, box-shadow .2s;">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i>Daftar Penyetoran ({{ $penyetoran->total() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Petani</th>
                        <th>Supplier</th>
                        <th>Komoditas</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penyetoran as $p)
                    <tr>
                        <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $p->user->name }}</strong>
                            <br><small class="text-muted">{{ $p->user->email }}</small>
                        </td>
                        <td>{{ $p->supplier->name }}</td>
                        <td>{{ $p->komoditas->name }} {{ $p->komoditas->grade ? '('.$p->komoditas->grade.')' : '' }}</td>
                        <td>{{ number_format($p->jumlah, 0) }} {{ $p->satuan }}</td>
                        <td class="fw-bold text-success">Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $p->status=='completed' ? 'bg-success' : ($p->status=='pending' ? 'bg-warning' : 'bg-danger') }}">
                                {{ $p->status === 'completed' ? 'Selesai' : ($p->status === 'pending' ? 'Menunggu' : 'Dibatalkan') }}
                            </span>
                        </td>
                        <td>
                            @if($p->status === 'pending')
                            <div class="d-flex gap-1 flex-wrap">
                                <form method="POST" action="{{ route('admin.penyetoran.update-status', $p) }}" onsubmit="return confirm('Tandai penyetoran ini selesai?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-sm btn-success" title="Tandai Selesai">
                                        <i class="fas fa-check me-1"></i>Selesai
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.penyetoran.update-status', $p) }}" onsubmit="return confirm('Batalkan penyetoran ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan Penyetoran">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-muted small">Selesai diproses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>Belum ada data penyetoran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $penyetoran->appends(request()->query())->links() }}
    </div>
</div>
@endsection
