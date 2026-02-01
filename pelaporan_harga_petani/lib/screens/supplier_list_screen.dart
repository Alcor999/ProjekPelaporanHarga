import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme/app_theme.dart';
import '../providers/supplier_provider.dart';
import '../providers/laporan_provider.dart';
import 'supplier_detail_screen.dart';
import 'penyetoran_list_screen.dart';

class SupplierListScreen extends StatefulWidget {
  const SupplierListScreen({Key? key}) : super(key: key);

  @override
  State<SupplierListScreen> createState() => _SupplierListScreenState();
}

class _SupplierListScreenState extends State<SupplierListScreen> {
  int? _filterWilayah;
  int? _filterKomoditas;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<LaporanProvider>().initReferenceData();
      context.read<SupplierProvider>().loadSuppliers(wilayahId: _filterWilayah, komoditasId: _filterKomoditas);
    });
  }

  void _applyFilter() {
    context.read<SupplierProvider>().loadSuppliers(wilayahId: _filterWilayah, komoditasId: _filterKomoditas);
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
          'Setor Hasil Panen',
          style: TextStyle(color: AppColors.white, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.history, color: AppColors.white),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PenyetoranListScreen())),
          ),
        ],
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
                    return Column(
                      children: [
                        DropdownButtonFormField<int?>(
                          value: _filterWilayah,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            filled: true,
                            fillColor: AppColors.white,
                          ),
                          hint: const Text('Semua Wilayah'),
                          items: [
                            const DropdownMenuItem(value: null, child: Text('Semua Wilayah')),
                            ...lp.wilayahList.map((w) => DropdownMenuItem(value: w.id, child: Text(w.displayName, overflow: TextOverflow.ellipsis))),
                          ],
                          onChanged: (v) {
                            setState(() {
                              _filterWilayah = v;
                              _applyFilter();
                            });
                          },
                        ),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<int?>(
                          value: _filterKomoditas,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            filled: true,
                            fillColor: AppColors.white,
                          ),
                          hint: const Text('Semua Komoditas'),
                          items: [
                            const DropdownMenuItem(value: null, child: Text('Semua Komoditas')),
                            ...lp.komoditasList.map((k) => DropdownMenuItem(value: k.id, child: Text(k.displayName))),
                          ],
                          onChanged: (v) {
                            setState(() {
                              _filterKomoditas = v;
                              _applyFilter();
                            });
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
            child: Consumer<SupplierProvider>(
              builder: (context, sp, _) {
                if (sp.loading && sp.supplierList.isEmpty) {
                  return const Center(child: CircularProgressIndicator(color: AppColors.primary));
                }
                if (sp.error != null && sp.supplierList.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 48, color: AppColors.error),
                        const SizedBox(height: 16),
                        Text(sp.error!, textAlign: TextAlign.center, style: TextStyle(color: AppColors.error)),
                      ],
                    ),
                  );
                }
                if (sp.supplierList.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.store, size: 64, color: AppColors.grey400),
                        const SizedBox(height: 16),
                        Text('Belum ada supplier', style: AppTextStyles.bodyLarge.copyWith(color: AppColors.grey600)),
                      ],
                    ),
                  );
                }
                return RefreshIndicator(
                  onRefresh: () => sp.loadSuppliers(wilayahId: _filterWilayah, komoditasId: _filterKomoditas),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    itemCount: sp.supplierList.length,
                    itemBuilder: (context, i) {
                      final s = sp.supplierList[i];
                      return _buildSupplierCard(context, s);
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

  Widget _buildSupplierCard(BuildContext context, Map<String, dynamic> s) {
    final name = s['name'] as String? ?? '-';
    final avgRating = (s['avg_rating'] as num?)?.toDouble() ?? 0;
    final ratingsCount = s['ratings_count'] as int? ?? 0;
    final harga = (s['harga'] as List?)?.cast<Map<String, dynamic>>() ?? [];
    final id = s['id'] as int? ?? 0;

    return Card(
      margin: const EdgeInsets.only(bottom: AppSpacing.md),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 2,
      child: InkWell(
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => SupplierDetailScreen(supplierId: id))),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.md),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                    child: Icon(Icons.store, color: AppColors.primary, size: 28),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name, style: AppTextStyles.titleMedium),
                        if (avgRating > 0)
                          Row(
                            children: [
                              Icon(Icons.star, color: AppColors.warning, size: 16),
                              const SizedBox(width: 4),
                              Text('${avgRating.toStringAsFixed(1)} ($ratingsCount ulasan)', style: AppTextStyles.bodySmall),
                            ],
                          ),
                      ],
                    ),
                  ),
                  Icon(Icons.chevron_right, color: AppColors.grey500),
                ],
              ),
              if (harga.isNotEmpty) ...[
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: harga.take(3).map((h) {
                    final komo = h['komoditas'] as Map?;
                    final komoName = komo?['name'] ?? '?';
                    final hrga = (h['harga_per_satuan'] as num?)?.toDouble() ?? 0;
                    final satuan = h['satuan'] as String? ?? 'kg';
                    return Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppColors.success.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text('$komoName: Rp ${hrga.toStringAsFixed(0)}/$satuan', style: AppTextStyles.labelSmall.copyWith(color: AppColors.success)),
                    );
                  }).toList(),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
