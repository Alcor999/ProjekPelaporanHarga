@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt text-primary"></i> Dashboard Admin
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <a href="{{ route('admin.statistik.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chart-bar me-1"></i> Lihat Statistik Lengkap
        </a>
    </div>
    <!-- Total Petani -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white shadow-sm stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Petani</h5>
                        <h2 class="mb-0">{{ number_format($stats['total_petani']) }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Laporan -->
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white shadow-sm stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Laporan</h5>
                        <h2 class="mb-0">{{ number_format($stats['total_laporan']) }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Pending -->
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white shadow-sm stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Pending Review</h5>
                        <h2 class="mb-0">{{ number_format($stats['laporan_pending']) }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Approved -->
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white shadow-sm stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Approved</h5>
                        <h2 class="mb-0">{{ number_format($stats['laporan_approved']) }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Wilayah & Komoditas -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marker-alt"></i> Master Data
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($stats['total_wilayah']) }}</h3>
                            <p class="text-muted mb-0">Wilayah</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($stats['total_komoditas']) }}</h3>
                            <p class="text-muted mb-0">Komoditas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Kelola Laporan Harga
                    </a>
                    <a href="{{ route('admin.broadcast.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-bullhorn"></i> Kirim Broadcast
                    </a>
                    <a href="{{ route('public.index') }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Lihat Homepage Publik
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line"></i> Grafik Tren Harga (14 Hari Terakhir)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="trenChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trenChart');
    const data = @json($trenHarga);
    if (ctx && data && data.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.tanggal),
                datasets: [{
                    label: 'Harga Rata-rata (Rp)',
                    data: data.map(d => parseFloat(d.harga_rata)),
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return 'Rp ' + parseInt(ctx.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush