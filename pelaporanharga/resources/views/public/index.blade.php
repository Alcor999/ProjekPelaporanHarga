<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harga Komoditas Terkini - Pelaporan Harga</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .komoditas-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 16px;
            overflow: hidden;
        }
        .komoditas-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15) !important;
        }
        .wilayah-row {
            transition: background-color 0.2s;
            border-radius: 8px;
        }
        .wilayah-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .harga-naik { color: #198754; }
        .harga-turun { color: #dc3545; }
        .harga-sama { color: #6c757d; }
        .badge-termurah { background: linear-gradient(135deg, #198754, #20c997); }
        .badge-termahal { background: linear-gradient(135deg, #dc3545, #fd7e14); }
        .price-compare-bar {
            height: 6px;
            border-radius: 3px;
            background: linear-gradient(90deg, #198754, #ffc107, #dc3545);
            position: relative;
        }
        .price-marker {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: absolute;
            top: -3px;
            transform: translateX(-50%);
        }
        #harga-container {
            transition: opacity 0.2s ease;
        }
        .auto-refresh {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .stat-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
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
                        <i class="fas fa-chart-line"></i> Harga Komoditas Terkini
                    </h1>
                    <p class="mb-0 mt-2">Perbandingan harga komoditas dari berbagai wilayah — Data terupdate otomatis</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <div>
                            <i class="fas fa-clock me-2"></i>
                            <small id="last-update">Terakhir update: {{ now()->format('d/m/Y H:i') }}</small>
                        </div>
                        <a href="/panel/login" class="btn btn-light btn-sm" target="_self">
                            <i class="fas fa-sign-in-alt me-1"></i> Login Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <!-- Filter (Live) -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5 col-6">
                        <label for="wilayah_id" class="form-label small text-muted mb-0">
                            <i class="fas fa-map-marker-alt text-primary me-1"></i> Filter Wilayah
                        </label>
                        <select id="wilayah_id" class="form-select form-select-sm">
                            <option value="">Semua Wilayah (Perbandingan)</option>
                            @foreach($wilayah as $w)
                                <option value="{{ $w->id }}" {{ $wilayahId == $w->id ? 'selected' : '' }}>
                                    {{ $w->kabupaten }} - {{ $w->kecamatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 col-6">
                        <label for="komoditas_id" class="form-label small text-muted mb-0">
                            <i class="fas fa-seedling text-success me-1"></i> Filter Komoditas
                        </label>
                        <select id="komoditas_id" class="form-select form-select-sm">
                            <option value="">Semua Komoditas</option>
                            @foreach($komoditas as $k)
                                <option value="{{ $k->id }}" {{ $komoditasId == $k->id ? 'selected' : '' }}>
                                    {{ ucfirst($k->kategori ?? 'padi') }} - {{ $k->name }} {{ $k->grade ? '('.$k->grade.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="/" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Info -->
        <div class="alert alert-info mb-4 d-flex align-items-center shadow-sm border-0">
            <i class="fas fa-info-circle fa-lg me-3"></i>
            <div>
                <strong>Harga Terbaru</strong> — Menampilkan harga terbaru yang sudah diverifikasi admin dari semua periode. Auto refresh setiap 15 detik.
                <span class="auto-refresh ms-2"><i class="fas fa-sync-alt"></i></span>
            </div>
        </div>

        <!-- Harga Cards - Grouped by Komoditas -->
        <div id="harga-container">
            @if($hargaPerKomoditas->count() > 0)
                @foreach($hargaPerKomoditas as $item)
                    @php
                        $wilayahData = collect($item['wilayah_data']);
                        $hargaTerendah = $item['harga_terendah'];
                        $hargaTertinggi = $item['harga_tertinggi'];
                        $range = $hargaTertinggi - $hargaTerendah;
                    @endphp
                    <div class="card komoditas-card mb-4 shadow">
                        <div class="card-header bg-primary text-white py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-seedling me-2"></i>
                                        {{ $item['komoditas']->name }}
                                        @if($item['komoditas']->grade)
                                            <small class="opacity-75">({{ $item['komoditas']->grade }})</small>
                                        @endif
                                    </h5>
                                    <small class="opacity-75">{{ ucfirst($item['komoditas']->kategori ?? 'padi') }}</small>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-light text-dark stat-badge">
                                        <i class="fas fa-map me-1"></i> {{ $item['total_wilayah'] }} wilayah
                                    </span>
                                    <span class="badge bg-success stat-badge">
                                        <i class="fas fa-arrow-down me-1"></i> Rp {{ number_format($hargaTerendah, 0, ',', '.') }}
                                    </span>
                                    <span class="badge bg-danger stat-badge">
                                        <i class="fas fa-arrow-up me-1"></i> Rp {{ number_format($hargaTertinggi, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Wilayah</th>
                                            <th class="text-end">Harga Terbaru</th>
                                            <th class="text-center">Perubahan</th>
                                            <th class="text-center" style="width: 120px;">Posisi Harga</th>
                                            <th class="text-center">Update</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($wilayahData as $wd)
                                            @php
                                                $posisi = $range > 0 ? (($wd['harga_terbaru'] - $hargaTerendah) / $range) * 100 : 50;
                                                $isTerMurah = $wd['harga_terbaru'] == $hargaTerendah && $item['total_wilayah'] > 1;
                                                $isTerMahal = $wd['harga_terbaru'] == $hargaTertinggi && $item['total_wilayah'] > 1;
                                            @endphp
                                            <tr class="wilayah-row">
                                                <td class="ps-3">
                                                    <strong>{{ $wd['wilayah']->kabupaten }}</strong>
                                                    <br><small class="text-muted">{{ $wd['wilayah']->kecamatan }}</small>
                                                    @if($isTerMurah)
                                                        <span class="badge badge-termurah ms-1"><i class="fas fa-thumbs-up"></i> Termurah</span>
                                                    @elseif($isTerMahal)
                                                        <span class="badge badge-termahal ms-1"><i class="fas fa-fire"></i> Termahal</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <strong class="fs-5">Rp {{ number_format($wd['harga_terbaru'], 0, ',', '.') }}</strong>
                                                    <small class="text-muted">/kg</small>
                                                </td>
                                                <td class="text-center">
                                                    @if($wd['perubahan'] !== null)
                                                        @if($wd['perubahan'] > 0)
                                                            <span class="harga-naik">
                                                                <i class="fas fa-arrow-up"></i>
                                                                +{{ number_format($wd['perubahan'], 0, ',', '.') }}
                                                                <small>(+{{ $wd['perubahan_persen'] }}%)</small>
                                                            </span>
                                                        @elseif($wd['perubahan'] < 0)
                                                            <span class="harga-turun">
                                                                <i class="fas fa-arrow-down"></i>
                                                                {{ number_format($wd['perubahan'], 0, ',', '.') }}
                                                                <small>({{ $wd['perubahan_persen'] }}%)</small>
                                                            </span>
                                                        @else
                                                            <span class="harga-sama">
                                                                <i class="fas fa-minus"></i> Sama
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['total_wilayah'] > 1)
                                                    <div class="price-compare-bar">
                                                        <div class="price-marker" style="left: {{ $posisi }}%"></div>
                                                    </div>
                                                    @else
                                                    <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($wd['tanggal_update'])->format('d/m/Y') }}
                                                    </small>
                                                    <br>
                                                    <small class="badge bg-secondary">{{ $wd['jumlah_laporan'] }} laporan</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Tidak ada data harga</h4>
                    <p class="text-muted">Belum ada laporan harga yang disetujui untuk kriteria yang dipilih</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Pelaporan Harga Komoditas</h5>
                    <p class="mb-0">Sistem informasi harga komoditas real-time untuk membantu petani dan pembeli</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">&copy; {{ date('Y') }} Pelaporan Harga Komoditas. All rights reserved.</p>
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
            komoditas_id: '{{ $komoditasId }}'
        };

        function refreshHargaData() {
            const container = document.getElementById('harga-container');
            container.style.opacity = '0.6';
            fetch(`{{ route('api.public.harga-terkini') }}?${new URLSearchParams(currentFilters)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateHargaDisplay(data.data);
                        document.getElementById('last-update').textContent =
                            'Terakhir update: ' + new Date().toLocaleString('id-ID');
                    }
                    container.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    container.style.opacity = '1';
                });
        }

        function updateHargaDisplay(hargaData) {
            const container = document.getElementById('harga-container');

            if (!hargaData || hargaData.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada data harga</h4>
                        <p class="text-muted">Belum ada laporan harga yang disetujui untuk kriteria yang dipilih</p>
                    </div>
                `;
                return;
            }

            let html = '';

            hargaData.forEach(item => {
                const wilayahData = item.wilayah_data || [];
                const hargaTerendah = item.harga_terendah;
                const hargaTertinggi = item.harga_tertinggi;
                const range = hargaTertinggi - hargaTerendah;

                html += `
                    <div class="card komoditas-card mb-4 shadow">
                        <div class="card-header bg-primary text-white py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-seedling me-2"></i>
                                        ${item.komoditas.name}
                                        ${item.komoditas.grade ? `<small class="opacity-75">(${item.komoditas.grade})</small>` : ''}
                                    </h5>
                                    <small class="opacity-75">${item.komoditas.kategori ? item.komoditas.kategori.charAt(0).toUpperCase() + item.komoditas.kategori.slice(1) : 'Padi'}</small>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-light text-dark stat-badge">
                                        <i class="fas fa-map me-1"></i> ${item.total_wilayah} wilayah
                                    </span>
                                    <span class="badge bg-success stat-badge">
                                        <i class="fas fa-arrow-down me-1"></i> Rp ${new Intl.NumberFormat('id-ID').format(hargaTerendah)}
                                    </span>
                                    <span class="badge bg-danger stat-badge">
                                        <i class="fas fa-arrow-up me-1"></i> Rp ${new Intl.NumberFormat('id-ID').format(hargaTertinggi)}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Wilayah</th>
                                            <th class="text-end">Harga Terbaru</th>
                                            <th class="text-center">Perubahan</th>
                                            <th class="text-center" style="width: 120px;">Posisi Harga</th>
                                            <th class="text-center">Update</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${wilayahData.map(wd => {
                                            const posisi = range > 0 ? ((wd.harga_terbaru - hargaTerendah) / range) * 100 : 50;
                                            const isTerMurah = wd.harga_terbaru == hargaTerendah && item.total_wilayah > 1;
                                            const isTerMahal = wd.harga_terbaru == hargaTertinggi && item.total_wilayah > 1;

                                            let perubahanHtml = '<span class="text-muted">-</span>';
                                            if (wd.perubahan !== null) {
                                                if (wd.perubahan > 0) {
                                                    perubahanHtml = `<span class="harga-naik"><i class="fas fa-arrow-up"></i> +${new Intl.NumberFormat('id-ID').format(wd.perubahan)} <small>(+${wd.perubahan_persen}%)</small></span>`;
                                                } else if (wd.perubahan < 0) {
                                                    perubahanHtml = `<span class="harga-turun"><i class="fas fa-arrow-down"></i> ${new Intl.NumberFormat('id-ID').format(wd.perubahan)} <small>(${wd.perubahan_persen}%)</small></span>`;
                                                } else {
                                                    perubahanHtml = '<span class="harga-sama"><i class="fas fa-minus"></i> Sama</span>';
                                                }
                                            }

                                            return `
                                                <tr class="wilayah-row">
                                                    <td class="ps-3">
                                                        <strong>${wd.wilayah.kabupaten}</strong>
                                                        <br><small class="text-muted">${wd.wilayah.kecamatan}</small>
                                                        ${isTerMurah ? '<span class="badge badge-termurah ms-1"><i class="fas fa-thumbs-up"></i> Termurah</span>' : ''}
                                                        ${isTerMahal ? '<span class="badge badge-termahal ms-1"><i class="fas fa-fire"></i> Termahal</span>' : ''}
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="fs-5">Rp ${new Intl.NumberFormat('id-ID').format(wd.harga_terbaru)}</strong>
                                                        <small class="text-muted">/kg</small>
                                                    </td>
                                                    <td class="text-center">${perubahanHtml}</td>
                                                    <td>
                                                        ${item.total_wilayah > 1 ? `
                                                            <div class="price-compare-bar">
                                                                <div class="price-marker" style="left: ${posisi}%"></div>
                                                            </div>
                                                        ` : '<span class="text-muted">-</span>'}
                                                    </td>
                                                    <td class="text-center">
                                                        <small class="text-muted">${new Date(wd.tanggal_update).toLocaleDateString('id-ID')}</small>
                                                        <br><small class="badge bg-secondary">${wd.jumlah_laporan} laporan</small>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Auto refresh every 15 seconds
        setInterval(refreshHargaData, 15000);

        // Live filter - refresh immediately on change
        function applyFilter() {
            currentFilters.wilayah_id = document.getElementById('wilayah_id').value;
            currentFilters.komoditas_id = document.getElementById('komoditas_id').value;
            refreshHargaData();
            history.replaceState(null, '', '?' + new URLSearchParams(currentFilters).toString());
        }

        document.getElementById('wilayah_id').addEventListener('change', applyFilter);
        document.getElementById('komoditas_id').addEventListener('change', applyFilter);
    </script>
</body>
</html>
