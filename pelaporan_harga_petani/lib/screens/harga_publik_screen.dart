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
  String? _tanggal;

  @override
  void initState() {
    super.initState();
    _tanggal = DateFormat('yyyy-MM-dd').format(DateTime.now());
    Future.microtask(() {
      context.read<LaporanProvider>().initReferenceData();
      context.read<HargaPublikProvider>().loadHargaTerkini(tanggal: _tanggal);
    });
  }

  void _applyFilter() {
    context.read<HargaPublikProvider>().loadHargaTerkini(
      wilayahId: _wilayahId,
      komoditasId: _komoditasId,
      tanggal: _tanggal,
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
                    Text('Filter live', style: AppTextStyles.labelLarge.copyWith(color: AppColors.primary)),
                  ],
                ),
                const SizedBox(height: AppSpacing.sm),
                Consumer<LaporanProvider>(
                  builder: (context, lp, _) {
                    if (lp.wilayahList.isEmpty) lp.initReferenceData();
                    return Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<int?>(
                            value: _wilayahId,
                            decoration: InputDecoration(
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            ),
                            hint: const Text('Semua Wilayah'),
                            items: [
                              const DropdownMenuItem(value: null, child: Text('Semua Wilayah')),
                              ...lp.wilayahList.map((w) => DropdownMenuItem(value: w.id, child: Text(w.displayName, overflow: TextOverflow.ellipsis))),
                            ],
                            onChanged: (v) {
                              setState(() => _wilayahId = v);
                              _applyFilter();
                            },
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: DropdownButtonFormField<int?>(
                            value: _komoditasId,
                            decoration: InputDecoration(
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
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
                        ),
                      ],
                    );
                  },
                ),
                const SizedBox(height: 8),
                InkWell(
                  onTap: () async {
                    final d = await showDatePicker(
                      context: context,
                      initialDate: _tanggal != null ? DateTime.parse(_tanggal!) : DateTime.now(),
                      firstDate: DateTime(2020),
                      lastDate: DateTime.now(),
                    );
                    if (d != null) {
                      setState(() => _tanggal = DateFormat('yyyy-MM-dd').format(d));
                      _applyFilter();
                    }
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    child: Row(
                      children: [
                        const Icon(Icons.calendar_today, color: AppColors.primary),
                        const SizedBox(width: 8),
                        Text(_tanggal ?? 'Pilih tanggal', style: AppTextStyles.bodyMedium),
                      ],
                    ),
                  ),
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
                        const Text('Belum ada data harga untuk filter ini'),
                      ],
                    ),
                  );
                }
                return RefreshIndicator(
                  onRefresh: () => hp.loadHargaTerkini(wilayahId: _wilayahId, komoditasId: _komoditasId, tanggal: _tanggal),
                  color: AppColors.primary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    itemCount: hp.hargaTerkini.length,
                    itemBuilder: (context, i) {
                      final h = hp.hargaTerkini[i];
                      final wilayah = h['wilayah'] as Map? ?? {};
                      final komoditas = h['komoditas'] as Map? ?? {};
                      final avg = (h['harga_rata_rata'] ?? 0).toDouble();
                      final min = (h['harga_terendah'] ?? 0).toDouble();
                      final max = (h['harga_tertinggi'] ?? 0).toDouble();
                      final jml = h['jumlah_laporan'] ?? 0;
                      return Card(
                        margin: const EdgeInsets.only(bottom: AppSpacing.md),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.md)),
                        child: Padding(
                          padding: const EdgeInsets.all(AppSpacing.md),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    wilayah['kabupaten'] ?? '-',
                                    style: AppTextStyles.titleMedium,
                                  ),
                                  Text(
                                    '${komoditas['name'] ?? ''} ${komoditas['grade'] != null ? '(${komoditas['grade']})' : ''}',
                                    style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text('Rata-rata', style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
                                      Text(
                                        'Rp ${NumberFormat('#,###', 'id_ID').format(avg)}',
                                        style: AppTextStyles.titleLarge.copyWith(color: AppColors.success, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Text('Terendah', style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
                                      Text('Rp ${NumberFormat('#,###', 'id_ID').format(min)}', style: AppTextStyles.bodyMedium),
                                    ],
                                  ),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Text('Tertinggi', style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
                                      Text('Rp ${NumberFormat('#,###', 'id_ID').format(max)}', style: AppTextStyles.bodyMedium),
                                    ],
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text('$jml laporan', style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey500)),
                            ],
                          ),
                        ),
                      );
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
}
