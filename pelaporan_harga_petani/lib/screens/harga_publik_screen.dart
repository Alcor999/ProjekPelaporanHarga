import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../core/theme/app_theme.dart';
import '../providers/harga_publik_provider.dart';
import '../providers/laporan_provider.dart';

class HargaPublikScreen extends StatefulWidget {
  const HargaPublikScreen({Key? key}) : super(key: key);

  @override
  State<HargaPublikScreen> createState() => _HargaPublikScreenState();
}

class _HargaPublikScreenState extends State<HargaPublikScreen> {
  int? _wilayahId;
  int? _komoditasId;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<LaporanProvider>().initReferenceData();
      context.read<HargaPublikProvider>().loadHargaTerkini();
    });
  }

  void _applyFilter() {
    context.read<HargaPublikProvider>().loadHargaTerkini(
      wilayahId: _wilayahId,
      komoditasId: _komoditasId,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Harga Pasar',
          style: TextStyle(color: AppColors.white, fontWeight: FontWeight.bold),
        ),
      ),
      body: Column(
        children: [
          Container(
            color: AppColors.primary.withValues(alpha: 0.08),
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.tune, color: AppColors.primary, size: 20),
                    const SizedBox(width: 8),
                    Text('Filter & Perbandingan Harga', style: AppTextStyles.labelLarge.copyWith(color: AppColors.primary)),
                  ],
                ),
                const SizedBox(height: AppSpacing.sm),
                Consumer<LaporanProvider>(
                  builder: (context, lp, _) {
                    if (lp.wilayahList.isEmpty) lp.initReferenceData();
                    return Column(
                      children: [
                        DropdownButtonFormField<int?>(
                          value: _wilayahId,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            filled: true,
                            fillColor: AppColors.white,
                          ),
                          hint: const Text('Semua Wilayah (Perbandingan)'),
                          items: [
                            const DropdownMenuItem(value: null, child: Text('Semua Wilayah (Perbandingan)')),
                            ...lp.wilayahList.map((w) => DropdownMenuItem(value: w.id, child: Text(w.displayName, overflow: TextOverflow.ellipsis))),
                          ],
                          onChanged: (v) {
                            setState(() => _wilayahId = v);
                            _applyFilter();
                          },
                        ),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<int?>(
                          value: _komoditasId,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            filled: true,
                            fillColor: AppColors.white,
                          ),
                          hint: const Text('Semua Komoditas'),
                          items: [
                            const DropdownMenuItem(value: null, child: Text('Semua Komoditas')),
                            ...lp.komoditasList.map((k) => DropdownMenuItem(value: k.id, child: Text(k.displayName, overflow: TextOverflow.ellipsis))),
                          ],
                          onChanged: (v) {
                            setState(() => _komoditasId = v);
                            _applyFilter();
                          },
                        ),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
          Expanded(
            child: Consumer<HargaPublikProvider>(
              builder: (context, hp, _) {
                if (hp.isLoading && hp.hargaTerkini.isEmpty) {
                  return const Center(child: CircularProgressIndicator(color: AppColors.primary));
                }
                if (hp.errorMessage != null && hp.hargaTerkini.isEmpty) {
                  return Center(child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 48, color: AppColors.grey500),
                      const SizedBox(height: 16),
                      Text(hp.errorMessage ?? 'Error', textAlign: TextAlign.center),
                    ],
                  ));
                }
                if (hp.hargaTerkini.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.inbox, size: 64, color: AppColors.grey400),
                        const SizedBox(height: 16),
                        Text('Belum ada data harga yang disetujui', style: AppTextStyles.bodyLarge.copyWith(color: AppColors.grey600)),
                      ],
                    ),
                  );
                }
                return RefreshIndicator(
                  onRefresh: () => hp.loadHargaTerkini(wilayahId: _wilayahId, komoditasId: _komoditasId),
                  color: AppColors.primary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    itemCount: hp.hargaTerkini.length,
                    itemBuilder: (context, i) {
                      final item = hp.hargaTerkini[i];
                      return _buildKomoditasCard(item);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKomoditasCard(dynamic item) {
    final komoditas = item['komoditas'] as Map? ?? {};
    final wilayahData = (item['wilayah_data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
    final hargaTerendah = (item['harga_terendah'] as num?)?.toDouble() ?? 0;
    final hargaTertinggi = (item['harga_tertinggi'] as num?)?.toDouble() ?? 0;
    final totalWilayah = item['total_wilayah'] as int? ?? 0;
    final range = hargaTertinggi - hargaTerendah;

    return Card(
      margin: const EdgeInsets.only(bottom: AppSpacing.lg),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 4,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header - Komoditas info
          Container(
            padding: const EdgeInsets.all(AppSpacing.md),
            decoration: BoxDecoration(
              color: AppColors.primary,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.grass, color: AppColors.white, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${komoditas['name'] ?? '-'} ${komoditas['grade'] != null ? '(${komoditas['grade']})' : ''}',
                        style: const TextStyle(color: AppColors.white, fontWeight: FontWeight.bold, fontSize: 18),
                      ),
                      Text(
                        komoditas['kategori'] != null ? komoditas['kategori'].toString().toUpperCase() : 'PADI',
                        style: TextStyle(color: AppColors.white.withValues(alpha: 0.8), fontSize: 12),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text('$totalWilayah wilayah', style: const TextStyle(color: AppColors.white, fontSize: 12)),
                ),
              ],
            ),
          ),
          // Summary row
          Container(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.sm),
            color: AppColors.grey100,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildSummaryItem('Terendah', hargaTerendah, AppColors.success),
                Container(width: 1, height: 30, color: AppColors.grey300),
                _buildSummaryItem('Tertinggi', hargaTertinggi, AppColors.error),
              ],
            ),
          ),
          // Wilayah list
          ...wilayahData.map((wd) => _buildWilayahRow(wd, hargaTerendah, hargaTertinggi, range, totalWilayah)),
        ],
      ),
    );
  }

  Widget _buildSummaryItem(String label, double value, Color color) {
    return Column(
      children: [
        Text(label, style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
        const SizedBox(height: 2),
        Text(
          'Rp ${NumberFormat('#,###', 'id_ID').format(value)}',
          style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 16),
        ),
      ],
    );
  }

  Widget _buildWilayahRow(Map<String, dynamic> wd, double hargaTerendah, double hargaTertinggi, double range, int totalWilayah) {
    final wilayah = wd['wilayah'] as Map? ?? {};
    final hargaTerbaru = (wd['harga_terbaru'] as num?)?.toDouble() ?? 0;
    final perubahan = wd['perubahan'] as num?;
    final perubahanPersen = wd['perubahan_persen'] as num?;
    final tanggalUpdate = wd['tanggal_update'] as String?;
    final jumlahLaporan = wd['jumlah_laporan'] as int? ?? 0;

    final posisi = range > 0 ? ((hargaTerbaru - hargaTerendah) / range) * 100 : 50;
    final isTerMurah = hargaTerbaru == hargaTerendah && totalWilayah > 1;
    final isTerMahal = hargaTerbaru == hargaTertinggi && totalWilayah > 1;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.sm),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: AppColors.grey200)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(wilayah['kabupaten'] ?? '-', style: AppTextStyles.labelLarge),
                        if (isTerMurah) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(colors: [Color(0xFF198754), Color(0xFF20c997)]),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text('Termurah', style: TextStyle(color: AppColors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                          ),
                        ],
                        if (isTerMahal) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(colors: [Color(0xFFdc3545), Color(0xFFfd7e14)]),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text('Termahal', style: TextStyle(color: AppColors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ],
                    ),
                    Text(wilayah['kecamatan'] ?? '', style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    'Rp ${NumberFormat('#,###', 'id_ID').format(hargaTerbaru)}',
                    style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
                  ),
                  if (perubahan != null)
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          perubahan > 0 ? Icons.arrow_upward : (perubahan < 0 ? Icons.arrow_downward : Icons.remove),
                          size: 14,
                          color: perubahan > 0 ? AppColors.success : (perubahan < 0 ? AppColors.error : AppColors.grey500),
                        ),
                        Text(
                          perubahan > 0 ? '+${NumberFormat('#,###', 'id_ID').format(perubahan)}' : NumberFormat('#,###', 'id_ID').format(perubahan),
                          style: TextStyle(
                            fontSize: 12,
                            color: perubahan > 0 ? AppColors.success : (perubahan < 0 ? AppColors.error : AppColors.grey500),
                          ),
                        ),
                        if (perubahanPersen != null)
                          Text(
                            ' (${perubahanPersen > 0 ? '+' : ''}${perubahanPersen.toStringAsFixed(1)}%)',
                            style: TextStyle(
                              fontSize: 10,
                              color: perubahan > 0 ? AppColors.success : (perubahan < 0 ? AppColors.error : AppColors.grey500),
                            ),
                          ),
                      ],
                    ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              if (totalWilayah > 1) ...[
                Expanded(
                  child: Container(
                    height: 6,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(3),
                      gradient: const LinearGradient(colors: [Color(0xFF198754), Color(0xFFffc107), Color(0xFFdc3545)]),
                    ),
                    child: Stack(
                      children: [
                        Positioned(
                          left: posisi.clamp(0, 100) / 100 * (MediaQuery.of(context).size.width - 100),
                          child: Container(
                            width: 12,
                            height: 12,
                            transform: Matrix4.translationValues(-6, -3, 0),
                            decoration: BoxDecoration(
                              color: AppColors.primary,
                              shape: BoxShape.circle,
                              border: Border.all(color: AppColors.white, width: 2),
                              boxShadow: [BoxShadow(color: Colors.black26, blurRadius: 4)],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
              ],
              Text(
                tanggalUpdate != null ? DateFormat('dd/MM/yy').format(DateTime.parse(tanggalUpdate)) : '-',
                style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey500),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(color: AppColors.grey200, borderRadius: BorderRadius.circular(4)),
                child: Text('$jumlahLaporan lap', style: const TextStyle(fontSize: 10, color: AppColors.grey600)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
