@extends('layouts.admin')

@section('title', 'Statistik Lengkap')

@push('styles')
<style>
    .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
    .chart-container { position: relative; height: 300px; }
    .live-badge { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .table-stat tr:hover { background: rgba(13, 110, 253, 0.05); }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="mb-0">
                <i class="fas fa-chart-bar text-primary"></i> Statistik Lengkap
                <span class="badge bg-success ms-2 live-badge"><i class="fas fa-circle"></i> Live</span>
            </h1>
        </div>
    </div>
</div>

<!-- Filter Live -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.statistik.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-0">Periode</label>
                <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7" {{ $periode == 7 ? 'selected' : '' }}>7 Hari</option>
                    <option value="14" {{ $periode == 14 ? 'selected' : '' }}>14 Hari</option>
                    <option value="30" {{ $periode == 30 ? 'selected' : '' }}>30 Hari</option>
                    <option value="60" {{ $periode == 60 ? 'selected' : '' }}>60 Hari</option>
                    <option value="90" {{ $periode == 90 ? 'selected' : '' }}>90 Hari</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Wilayah</label>
                <select name="wilayah_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Wilayah</option>
                    @foreach($wilayah as $w)
                    <option value="{{ $w->id }}" {{ $wilayahId == $w->id ? 'selected' : '' }}>{{ $w->kabupaten }} - {{ $w->kecamatan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Komoditas</label>
                <select name="komoditas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Komoditas</option>
                    @foreach($komoditas as $k)
                    <option value="{{ $k->id }}" {{ $komoditasId == $k->id ? 'selected' : '' }}>{{ $k->name }} {{ $k->grade ? '('.$k->grade.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.statistik.index') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync-alt"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4" id="statsCards">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title opacity-75">Total Petani</h6>
                        <h2 class="mb-0" id="stat-petani">{{ number_format($stats['total_petani']) }}</h2>
                        <small>Aktif: {{ number_format($stats['petani_aktif']) }}</small>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title opacity-75">Total Laporan</h6>
                        <h2 class="mb-0" id="stat-laporan">{{ number_format($stats['total_laporan']) }}</h2>
                        <small>Periode: {{ number_format($stats['laporan_periode']) }}</small>
                    </div>
                    <i class="fas fa-file-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title opacity-75">Status Laporan</h6>
                        <small>Pending: {{ $stats['laporan_pending'] }} | Approved: {{ $stats['laporan_approved'] }} | Rejected: {{ $stats['laporan_rejected'] }}</small>
                    </div>
                    <i class="fas fa-tasks fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title opacity-75">Harga Rata Periode</h6>
                        <h4 class="mb-0" id="stat-harga">Rp {{ number_format($stats['harga_rata_periode'], 0, ',', '.') }}</h4>
                        <small>/kg</small>
                    </div>
                    <i class="fas fa-coins fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Grafik Tren -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Tren Harga ({{ $periode }} Hari)</h5>
                <small class="text-muted">Auto refresh 30 detik</small>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trenChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Per Kategori -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-layer-group text-success"></i> Per Kategori</h5>
            </div>
            <div class="card-body p-0">
                @forelse($perKategori as $pk)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <span class="badge bg-info">{{ ucfirst($pk->kategori ?? 'lainnya') }}</span>
                    <div class="text-end">
                        <strong>{{ $pk->jumlah }}</strong> laporan
                        <br><small class="text-success">Rp {{ number_format($pk->harga_rata ?? 0, 0, ',', '.') }}/kg</small>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4">Belum ada data</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Per Wilayah -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt text-danger"></i> Top 10 Wilayah</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-stat mb-0">
                        <thead>
                            <tr><th>Wilayah</th><th>Laporan</th><th>Rata-rata</th><th>Min-Max</th></tr>
                        </thead>
                        <tbody>
                            @forelse($perWilayah as $pw)
                            <tr>
                                <td><strong>{{ $pw->wilayah->kabupaten ?? '-' }}</strong><br><small>{{ $pw->wilayah->kecamatan ?? '' }}</small></td>
                                <td>{{ $pw->jumlah_laporan }}</td>
                                <td class="text-success">Rp {{ number_format($pw->harga_rata ?? 0, 0, ',', '.') }}</td>
                                <td><small>Rp {{ number_format($pw->harga_min ?? 0, 0, ',', '.') }} - Rp {{ number_format($pw->harga_max ?? 0, 0, ',', '.') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Per Komoditas -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-seedling text-success"></i> Top 10 Komoditas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-stat mb-0">
                        <thead>
                            <tr><th>Komoditas</th><th>Laporan</th><th>Rata-rata</th><th>Min-Max</th></tr>
                        </thead>
                        <tbody>
                            @forelse($perKomoditas as $pk)
                            <tr>
                                <td><strong>{{ $pk->komoditas->name ?? '-' }}</strong> {{ $pk->komoditas->grade ? '('.$pk->komoditas->grade.')' : '' }}<br><small class="badge bg-secondary">{{ $pk->komoditas->kategori ?? '' }}</small></td>
                                <td>{{ $pk->jumlah_laporan }}</td>
                                <td class="text-success">Rp {{ number_format($pk->harga_rata ?? 0, 0, ',', '.') }}</td>
                                <td><small>Rp {{ number_format($pk->harga_min ?? 0, 0, ',', '.') }} - Rp {{ number_format($pk->harga_max ?? 0, 0, ',', '.') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Petani -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-trophy text-warning"></i> Top 10 Petani Pelapor</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>#</th><th>Nama</th><th>Email</th><th>Total Laporan</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topPetani as $i => $tp)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $tp->name }}</td>
                                <td>{{ $tp->email }}</td>
                                <td><span class="badge bg-primary">{{ $tp->total_laporan }}</span></td>
                            </tr>
                            @endforeach
                            @if(count($topPetani) == 0)
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let trenData = @json($trenHarga);
    let trenChart = null;

    function initChart() {
        const ctx = document.getElementById('trenChart');
        if (!ctx) return;
        if (trenChart) trenChart.destroy();

        if (trenData && trenData.length > 0) {
            trenChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trenData.map(d => d.tanggal),
                    datasets: [{
                        label: 'Harga Rata (Rp)',
                        data: trenData.map(d => parseFloat(d.harga_rata)),
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }
                        }
                    }
                }
            });
        }
    }
    initChart();

    // Live refresh stats (setiap 30 detik)
    function refreshStats() {
        fetch('{{ url("/admin/statistik-live") }}?periode={{ $periode }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const s = data.stats;
                    const el = id => document.getElementById(id);
                    if (el('stat-petani')) el('stat-petani').textContent = s.total_petani?.toLocaleString() || '0';
                    if (el('stat-laporan')) el('stat-laporan').textContent = s.total_laporan?.toLocaleString() || '0';
                    if (el('stat-harga')) el('stat-harga').textContent = 'Rp ' + (s.harga_rata || 0).toLocaleString('id-ID');
                    if (data.tren && data.tren.length > 0) {
                        trenData = data.tren;
                        initChart();
                    }
                }
            })
            .catch(() => {});
    }
    setInterval(refreshStats, 30000);
});
</script>
@endpush
