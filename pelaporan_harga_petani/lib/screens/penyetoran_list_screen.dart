import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../core/theme/app_theme.dart';
import '../providers/penyetoran_provider.dart';
import '../widgets/rating_sheet.dart';

class PenyetoranListScreen extends StatefulWidget {
  const PenyetoranListScreen({super.key});

  @override
  State<PenyetoranListScreen> createState() => _PenyetoranListScreenState();
}

class _PenyetoranListScreenState extends State<PenyetoranListScreen> {
  String? _filterStatus;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        context.read<PenyetoranProvider>().loadPenyetoranList(status: _filterStatus);
      }
    });
  }

  Color _statusBgColor(String status) {
    switch (status) {
      case 'completed':
        return AppColors.success;
      case 'cancelled':
        return AppColors.error;
      case 'pending':
        return AppColors.warningSurface;
      default:
        return AppColors.grey600;
    }
  }

  Color _statusFgColor(String status) {
    return status == 'pending' ? AppColors.warningInk : AppColors.white;
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'completed':
        return 'SELESAI';
      case 'cancelled':
        return 'DIBATALKAN';
      case 'pending':
        return 'MENUNGGU';
      default:
        return status.toUpperCase();
    }
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
          'Riwayat Penyetoran',
          style: TextStyle(color: AppColors.white, fontWeight: FontWeight.bold),
        ),
      ),
      body: Column(
        children: [
          Container(
            color: AppColors.primary.withValues(alpha: 0.08),
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Row(
              children: [
                Icon(Icons.tune, color: AppColors.primary, size: 20),
                const SizedBox(width: 8),
                Text('Filter:', style: AppTextStyles.labelLarge),
                const SizedBox(width: 12),
                Expanded(
                  child: DropdownButtonFormField<String?>(
                    isExpanded: true,
                    value: _filterStatus,
                    decoration: InputDecoration(
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      filled: true,
                      fillColor: AppColors.white,
                    ),
                    items: const [
                      DropdownMenuItem(value: null, child: Text('Semua')),
                      DropdownMenuItem(value: 'pending', child: Text('Menunggu')),
                      DropdownMenuItem(value: 'completed', child: Text('Selesai')),
                      DropdownMenuItem(value: 'cancelled', child: Text('Dibatalkan')),
                    ],
                    onChanged: (v) {
                      setState(() {
                        _filterStatus = v;
                        context.read<PenyetoranProvider>().loadPenyetoranList(status: _filterStatus);
                      });
                    },
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: Consumer<PenyetoranProvider>(
              builder: (context, pp, _) {
                if (pp.loading && pp.penyetoranList.isEmpty) {
                  return const Center(child: CircularProgressIndicator(color: AppColors.primary));
                }
                if (pp.error != null && pp.penyetoranList.isEmpty) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                          const SizedBox(height: 12),
                          Text(pp.error!, textAlign: TextAlign.center, style: AppTextStyles.bodyMedium),
                          const SizedBox(height: 16),
                          ElevatedButton.icon(
                            onPressed: () => pp.loadPenyetoranList(status: _filterStatus),
                            icon: const Icon(Icons.refresh),
                            label: const Text('Coba Lagi'),
                          ),
                        ],
                      ),
                    ),
                  );
                }
                if (pp.penyetoranList.isEmpty) {
                  return RefreshIndicator(
                    onRefresh: () => pp.loadPenyetoranList(status: _filterStatus),
                    color: AppColors.primary,
                    child: LayoutBuilder(
                      builder: (context, constraints) => SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        child: SizedBox(
                          height: constraints.maxHeight,
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.inventory_2, size: 64, color: AppColors.grey500),
                                const SizedBox(height: 16),
                                Text('Belum ada riwayat penyetoran', style: AppTextStyles.bodyLarge.copyWith(color: AppColors.grey600)),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  );
                }
                return RefreshIndicator(
                  onRefresh: () => pp.loadPenyetoranList(status: _filterStatus),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    itemCount: pp.penyetoranList.length,
                    itemBuilder: (context, i) {
                      final p = pp.penyetoranList[i];
                      return _buildPenyetoranCard(context, p);
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

  Widget _buildPenyetoranCard(BuildContext context, Map<String, dynamic> p) {
    final penyetoranId = (p['id'] as num?)?.toInt();
    final supplier = p['supplier'] as Map?;
    final supplierId = (supplier?['id'] as num?)?.toInt() ?? (p['supplier_id'] as num?)?.toInt();
    final supplierName = supplier?['name'] as String? ?? '-';
    final komoditas = p['komoditas'] as Map?;
    final komoName = komoditas?['name'] as String? ?? '?';
    final jumlah = (p['jumlah'] as num?)?.toDouble() ?? 0;
    final satuan = p['satuan'] as String? ?? 'kg';
    final total = (p['total_nilai'] as num?)?.toDouble() ?? 0;
    final tanggal = p['tanggal'] as String? ?? '';
    final status = p['status'] as String? ?? 'pending';
    final hasRating = p['has_rating'] == true;
    final canRate =
        status == 'completed' && !hasRating && penyetoranId != null && supplierId != null;

    return Card(
      margin: const EdgeInsets.only(bottom: AppSpacing.md),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.md),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    supplierName,
                    style: AppTextStyles.titleMedium,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: _statusBgColor(status),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _statusLabel(status),
                    style: TextStyle(
                      color: _statusFgColor(status),
                      fontWeight: FontWeight.bold,
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text('$komoName - ${jumlah.toStringAsFixed(0)} $satuan', style: AppTextStyles.bodyMedium),
            const SizedBox(height: 4),
            Text(
              'Rp ${NumberFormat('#,##0', 'id_ID').format(total)}',
              style: AppTextStyles.titleMedium.copyWith(color: AppColors.success),
            ),
            const SizedBox(height: 4),
            Text(tanggal, style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
            if (canRate) ...[
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    showSupplierRatingSheet(
                      context,
                      supplierId: supplierId,
                      penyetoranId: penyetoranId,
                      onSubmitted: () => context
                          .read<PenyetoranProvider>()
                          .loadPenyetoranList(status: _filterStatus),
                    );
                  },
                  icon: const Icon(Icons.star_outline, color: AppColors.warning),
                  label: const Text('Beri Rating'),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(double.infinity, 44),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
