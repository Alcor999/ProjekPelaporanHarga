import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../core/theme/app_theme.dart';
import '../providers/penyetoran_provider.dart';

class PenyetoranListScreen extends StatefulWidget {
  const PenyetoranListScreen({Key? key}) : super(key: key);

  @override
  State<PenyetoranListScreen> createState() => _PenyetoranListScreenState();
}

class _PenyetoranListScreenState extends State<PenyetoranListScreen> {
  String? _filterStatus;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<PenyetoranProvider>().loadPenyetoranList(status: _filterStatus));
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'completed': return AppColors.success;
      case 'pending': return AppColors.warning;
      default: return AppColors.grey500;
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
                    value: _filterStatus,
                    decoration: InputDecoration(
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      filled: true,
                      fillColor: AppColors.white,
                    ),
                    items: const [
                      DropdownMenuItem(value: null, child: Text('Semua')),
                      DropdownMenuItem(value: 'pending', child: Text('Pending')),
                      DropdownMenuItem(value: 'completed', child: Text('Completed')),
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
                if (pp.penyetoranList.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.inventory_2, size: 64, color: AppColors.grey400),
                        const SizedBox(height: 16),
                        Text('Belum ada riwayat penyetoran', style: AppTextStyles.bodyLarge.copyWith(color: AppColors.grey600)),
                      ],
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
                      return _buildPenyetoranCard(p);
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

  Widget _buildPenyetoranCard(Map<String, dynamic> p) {
    final supplier = p['supplier'] as Map?;
    final supplierName = supplier?['name'] ?? '-';
    final komoditas = p['komoditas'] as Map?;
    final komoName = komoditas?['name'] ?? '?';
    final jumlah = (p['jumlah'] as num?)?.toDouble() ?? 0;
    final satuan = p['satuan'] ?? 'kg';
    final total = (p['total_nilai'] as num?)?.toDouble() ?? 0;
    final tanggal = p['tanggal'] as String? ?? '';
    final status = p['status'] as String? ?? 'pending';

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
                Text(supplierName, style: AppTextStyles.titleMedium),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: _statusColor(status).withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(status.toUpperCase(), style: TextStyle(color: _statusColor(status), fontWeight: FontWeight.bold, fontSize: 11)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text('$komoName • ${jumlah.toStringAsFixed(0)} $satuan', style: AppTextStyles.bodyMedium),
            const SizedBox(height: 4),
            Text('Rp ${NumberFormat('#,###', 'id').format(total)}', style: AppTextStyles.titleMedium.copyWith(color: AppColors.success)),
            const SizedBox(height: 4),
            Text(tanggal, style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
          ],
        ),
      ),
    );
  }
}
