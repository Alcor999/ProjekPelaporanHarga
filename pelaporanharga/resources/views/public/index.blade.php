<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harga Padi Terkini - Pelaporan Harga Padi</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .harga-card {
            transition: transform 0.2s;
        }
        .harga-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .auto-refresh {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <header class="bg-primary text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-chart-line"></i> Harga Padi Terkini
                    </h1>
                    <p class="mb-0 mt-2">Informasi harga padi terkini dari petani di berbagai wilayah</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <div>
                            <i class="fas fa-clock me-2"></i>
                            <small id="last-update">Terakhir update: {{ now()->format('d/m/Y H:i') }}</small>
                        </div>
                        <a href="{{ route('admin.login') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i> Login Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('public.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="wilayah_id" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Wilayah
                        </label>
                        <select name="wilayah_id" id="wilayah_id" class="form-select">
                            <option value="">Semua Wilayah</option>
                            @foreach($wilayah as $w)
                                <option value="{{ $w->id }}" {{ $wilayahId == $w->id ? 'selected' : '' }}>
                                    {{ $w->kabupaten }} - {{ $w->kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="komoditas_id" class="form-label">
                            <i class="fas fa-seedling"></i> Komoditas
                        </label>
                        <select name="komoditas_id" id="komoditas_id" class="form-select">
                            <option value="">Semua Komoditas</option>
                            @foreach($komoditas as $k)
                                <option value="{{ $k->id }}" {{ $komoditasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->name }} {{ $k->grade ? '('.$k->grade.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="tanggal" class="form-label">
                            <i class="fas fa-calendar"></i> Tanggal
                        </label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control"
                               value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Info -->
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <div>
                    <strong>Informasi:</strong> Data harga diperbarui secara otomatis setiap 15 detik.
                    <span class="auto-refresh ms-2">
                        <i class="fas fa-sync-alt"></i> Auto refresh aktif
                    </span>
                </div>
            </div>
        </div>

        <!-- Harga Cards -->
        <div id="harga-container">
            @if($hargaAgregat->count() > 0)
                <div class="row">
                    @foreach($hargaAgregat as $harga)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card harga-card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $harga->wilayah->kabupaten }}
                                    </h5>
                                    <small>{{ $harga->wilayah->kecamatan }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <div class="text-center">
                                                <h3 class="text-success mb-1">
                                                    Rp {{ number_format($harga->harga_rata_rata, 0, ',', '.') }}
                                                </h3>
                                                <small class="text-muted">Harga Rata-rata per kg</small>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="border-end pe-2">
                                                <small class="text-muted d-block">Terendah</small>
                                                <strong class="text-danger">
                                                    Rp {{ number_format($harga->harga_terendah, 0, ',', '.') }}
                                                </strong>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="ps-2">
                                                <small class="text-muted d-block">Tertinggi</small>
                                                <strong class="text-success">
                                                    Rp {{ number_format($harga->harga_tertinggi, 0, ',', '.') }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Komoditas</small>
                                            <strong>{{ $harga->komoditas->name }}</strong>
                                            @if($harga->komoditas->grade)
                                                <br><small class="text-primary">{{ $harga->komoditas->grade }}</small>
                                            @endif
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block">Laporan</small>
                                            <strong>{{ $harga->jumlah_laporan }}</strong>
                                            <small>laporan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Tidak ada data harga</h4>
                    <p class="text-muted">Belum ada laporan harga untuk kriteria yang dipilih</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Pelaporan Harga Padi</h5>
                    <p class="mb-0">Sistem informasi harga padi real-time untuk membantu petani dan pembeli</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">&copy; {{ date('Y') }} Pelaporan Harga Padi. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto Refresh Script -->
    <script>
        let currentFilters = {
            wilayah_id: '{{ $wilayahId }}',
            komoditas_id: '{{ $komoditasId }}',
            tanggal: '{{ $tanggal }}'
        };

        function refreshHargaData() {
            fetch(`{{ route('api.public.harga-terkini') }}?${new URLSearchParams(currentFilters)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateHargaDisplay(data.data);
                        document.getElementById('last-update').textContent =
                            'Terakhir update: ' + new Date().toLocaleString('id-ID');
                    }
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                });
        }

        function updateHargaDisplay(hargaData) {
            const container = document.getElementById('harga-container');

            if (hargaData.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada data harga</h4>
                        <p class="text-muted">Belum ada laporan harga untuk kriteria yang dipilih</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="row">';

            hargaData.forEach(harga => {
                html += `
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card harga-card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                    ${harga.wilayah.kabupaten}
                                </h5>
                                <small>${harga.wilayah.kecamatan}</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="text-center">
                                            <h3 class="text-success mb-1">
                                                Rp ${new Intl.NumberFormat('id-ID').format(harga.harga_rata_rata)}
                                            </h3>
                                            <small class="text-muted">Harga Rata-rata per kg</small>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="border-end pe-2">
                                            <small class="text-muted d-block">Terendah</small>
                                            <strong class="text-danger">
                                                Rp ${new Intl.NumberFormat('id-ID').format(harga.harga_terendah)}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="ps-2">
                                            <small class="text-muted d-block">Tertinggi</small>
                                            <strong class="text-success">
                                                Rp ${new Intl.NumberFormat('id-ID').format(harga.harga_tertinggi)}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Komoditas</small>
                                        <strong>${harga.komoditas.name}</strong>
                                        ${harga.komoditas.grade ? `<br><small class="text-primary">${harga.komoditas.grade}</small>` : ''}
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted d-block">Laporan</small>
                                        <strong>${harga.jumlah_laporan}</strong>
                                        <small>laporan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;
        }

        // Auto refresh every 15 seconds
        setInterval(refreshHargaData, 15000);

        // Update filters when form changes
        document.getElementById('wilayah_id').addEventListener('change', function() {
            currentFilters.wilayah_id = this.value;
        });

        document.getElementById('komoditas_id').addEventListener('change', function() {
            currentFilters.komoditas_id = this.value;
        });

        document.getElementById('tanggal').addEventListener('change', function() {
            currentFilters.tanggal = this.value;
        });
    </script>
</body>
</html>
