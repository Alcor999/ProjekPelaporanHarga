import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../core/theme/app_theme.dart';
import '../providers/supplier_provider.dart';
import '../providers/penyetoran_provider.dart';
import '../widgets/rating_sheet.dart';

class SupplierDetailScreen extends StatefulWidget {
  final int supplierId;

  const SupplierDetailScreen({super.key, required this.supplierId});

  @override
  State<SupplierDetailScreen> createState() => _SupplierDetailScreenState();
}

class _SupplierDetailScreenState extends State<SupplierDetailScreen> {
  int? _selectedKomoditasId;
  final _jumlahController = TextEditingController();
  final _catatanController = TextEditingController();
  DateTime _selectedDate = DateTime.now();
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        context.read<SupplierProvider>().loadSupplierDetail(widget.supplierId);
      }
    });
  }

  @override
  void dispose() {
    _jumlahController.dispose();
    _catatanController.dispose();
    super.dispose();
  }

  Future<void> _submitPenyetoran() async {
    if (_selectedKomoditasId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih komoditas terlebih dahulu')),
      );
      return;
    }
    final jumlah = double.tryParse(_jumlahController.text.replaceAll(',', '.').trim());
    if (jumlah == null || jumlah <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Masukkan jumlah yang valid (> 0)')),
      );
      return;
    }
    setState(() => _submitting = true);
    final pp = context.read<PenyetoranProvider>();
    final success = await pp.createPenyetoran(
      supplierId: widget.supplierId,
      komoditasId: _selectedKomoditasId!,
      jumlah: jumlah,
      tanggal: DateFormat('yyyy-MM-dd').format(_selectedDate),
      catatan: _catatanController.text.trim().isEmpty
          ? null
          : _catatanController.text.trim(),
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Penyetoran berhasil dicatat'),
          backgroundColor: AppColors.success,
        ),
      );
      _jumlahController.clear();
      _catatanController.clear();
      final penyetoranId = pp.lastCreatedPenyetoranId;
      if (penyetoranId != null) {
        showSupplierRatingSheet(
          context,
          supplierId: widget.supplierId,
          penyetoranId: penyetoranId,
          onSubmitted: () => context
              .read<SupplierProvider>()
              .loadSupplierDetail(widget.supplierId),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(pp.error ?? 'Gagal mencatat penyetoran'),
          backgroundColor: AppColors.error,
        ),
      );
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
        title: const Text('Detail Supplier', style: TextStyle(color: AppColors.white, fontWeight: FontWeight.bold)),
      ),
      body: Consumer<SupplierProvider>(
        builder: (context, sp, _) {
          if (sp.loading && sp.supplierDetail == null) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }
          if (sp.error != null && sp.supplierDetail == null) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                    const SizedBox(height: 12),
                    Text(sp.error!, textAlign: TextAlign.center, style: AppTextStyles.bodyMedium),
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      onPressed: () => sp.loadSupplierDetail(widget.supplierId),
                      icon: const Icon(Icons.refresh),
                      label: const Text('Coba Lagi'),
                    ),
                  ],
                ),
              ),
            );
          }
          final s = sp.supplierDetail;
          if (s == null) {
            return const Center(child: Text('Supplier tidak ditemukan'));
          }
          final name = s['name'] as String? ?? '-';
          final phone = s['phone'] as String?;
          final address = s['address'] as String?;
          final avgRating = (s['avg_rating'] as num?)?.toDouble() ?? 0;
          final ratingsCount = s['ratings_count'] as int? ?? 0;
          final harga = (s['harga'] as List?)?.cast<Map<String, dynamic>>() ?? [];
          final jadwal = (s['jadwal'] as List?)?.cast<Map<String, dynamic>>() ?? [];
          final ratings = (s['ratings'] as List?)?.cast<Map<String, dynamic>>() ?? [];

          Map<String, dynamic>? selectedHarga;
          for (final h in harga) {
            if (h['komoditas_id'] == _selectedKomoditasId) {
              selectedHarga = h;
              break;
            }
          }
          final selectedSatuan = selectedHarga?['satuan'] as String? ?? 'kg';
          final selectedUnitPrice =
              (selectedHarga?['harga_per_satuan'] as num?)?.toDouble();
          final jumlahVal = double.tryParse(
            _jumlahController.text.replaceAll(',', '.').trim(),
          );
          final previewTotal =
              (selectedUnitPrice != null && jumlahVal != null && jumlahVal > 0)
                  ? selectedUnitPrice * jumlahVal
                  : null;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Card(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: Padding(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                              child: Icon(Icons.store, color: AppColors.primary, size: 32),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(name, style: AppTextStyles.titleLarge),
                                  if (avgRating > 0)
                                    Row(
                                      children: [
                                        Icon(Icons.star, color: AppColors.warning, size: 18),
                                        const SizedBox(width: 4),
                                        Text('${avgRating.toStringAsFixed(1)} ($ratingsCount ulasan)', style: AppTextStyles.bodySmall),
                                      ],
                                    ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        if (phone != null && phone.isNotEmpty) ...[
                          const SizedBox(height: 12),
                          Row(children: [
                            const Icon(Icons.phone, size: 18, color: AppColors.grey600),
                            const SizedBox(width: 8),
                            Expanded(child: Text(phone, style: AppTextStyles.bodyMedium)),
                          ]),
                        ],
                        if (address != null && address.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Row(children: [
                            const Icon(Icons.location_on, size: 18, color: AppColors.grey600),
                            const SizedBox(width: 8),
                            Expanded(child: Text(address, style: AppTextStyles.bodySmall)),
                          ]),
                        ],
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text('Harga per Komoditas', style: AppTextStyles.titleMedium),
                const SizedBox(height: 8),
                if (harga.isEmpty)
                  Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: Padding(
                      padding: const EdgeInsets.all(AppSpacing.md),
                      child: Text(
                        'Supplier ini belum menetapkan harga pembelian aktif.',
                        style: AppTextStyles.bodyMedium.copyWith(color: AppColors.grey600),
                      ),
                    ),
                  )
                else
                  ...harga.map((h) {
                    final komo = h['komoditas'] as Map?;
                    final komoName = komo?['name'] ?? '?';
                    final grade = komo?['grade'];
                    final hrga = (h['harga_per_satuan'] as num?)?.toDouble() ?? 0;
                    final satuan = h['satuan'] as String? ?? 'kg';
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        title: Text(
                          '$komoName${grade != null && grade.toString().isNotEmpty ? ' ($grade)' : ''}',
                        ),
                        subtitle: Text(
                          'Rp ${NumberFormat('#,##0', 'id_ID').format(hrga)}/$satuan',
                          style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.bold),
                        ),
                      ),
                    );
                  }),
                if (jadwal.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Text('Jadwal Operasional', style: AppTextStyles.titleMedium),
                  const SizedBox(height: 8),
                  ...jadwal.map((j) {
                    final namaHari = j['nama_hari'] as String? ?? 'Hari';
                    final jamRange = j['jam_range'] as String? ?? '-';
                    final isBuka = j['is_buka'] as bool? ?? true;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(child: Text(namaHari, style: AppTextStyles.bodyMedium)),
                          const SizedBox(width: AppSpacing.sm),
                          Text(
                            isBuka ? jamRange : 'Tutup',
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: isBuka ? AppColors.success : AppColors.grey600,
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
                const SizedBox(height: 24),
                if (harga.isNotEmpty)
                  Container(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.05),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Setor Hasil Panen', style: AppTextStyles.titleMedium.copyWith(color: AppColors.primary)),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<int?>(
                          isExpanded: true,
                          value: selectedHarga != null ? _selectedKomoditasId : null,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                            filled: true,
                            fillColor: AppColors.white,
                          ),
                          hint: const Text('Pilih Komoditas'),
                          items: harga.map((h) {
                            final kid = h['komoditas_id'] as int?;
                            final komo = h['komoditas'] as Map?;
                            final komoName = komo?['name'] ?? '?';
                            final grade = komo?['grade'];
                            final satuan = h['satuan'] as String? ?? 'kg';
                            return DropdownMenuItem(
                              value: kid,
                              child: Text(
                                '$komoName${grade != null && grade.toString().isNotEmpty ? ' ($grade)' : ''} ($satuan)',
                                overflow: TextOverflow.ellipsis,
                              ),
                            );
                          }).toList(),
                          onChanged: (v) => setState(() => _selectedKomoditasId = v),
                        ),
                        if (selectedUnitPrice != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            'Harga beli supplier: Rp ${NumberFormat('#,##0', 'id_ID').format(selectedUnitPrice)}/$selectedSatuan',
                            style: AppTextStyles.bodySmall.copyWith(
                              color: AppColors.success,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: _jumlahController,
                          onChanged: (_) => setState(() {}),
                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                          decoration: InputDecoration(
                            labelText: 'Jumlah ($selectedSatuan)',
                            hintText: '0',
                            border: const OutlineInputBorder(),
                            filled: true,
                            fillColor: Colors.white,
                          ),
                        ),
                        if (previewTotal != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            'Perkiraan total: Rp ${NumberFormat('#,##0', 'id_ID').format(previewTotal)}',
                            style: AppTextStyles.labelLarge.copyWith(color: AppColors.primaryDark),
                          ),
                        ],
                        const SizedBox(height: 12),
                        ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: const Text('Tanggal'),
                          subtitle: Text(DateFormat('dd/MM/yyyy').format(_selectedDate)),
                          trailing: IconButton(
                            tooltip: 'Pilih tanggal penyetoran',
                            icon: const Icon(Icons.calendar_today),
                            onPressed: () async {
                              final d = await showDatePicker(
                                context: context,
                                initialDate: _selectedDate,
                                firstDate: DateTime(2020),
                                lastDate: DateTime.now(),
                              );
                              if (d != null) setState(() => _selectedDate = d);
                            },
                          ),
                        ),
                        TextField(
                          controller: _catatanController,
                          decoration: const InputDecoration(
                            labelText: 'Catatan (opsional)',
                            border: OutlineInputBorder(),
                            filled: true,
                            fillColor: Colors.white,
                          ),
                          maxLines: 2,
                        ),
                        const SizedBox(height: 16),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton.icon(
                            onPressed: _submitting ? null : _submitPenyetoran,
                            icon: _submitting
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2),
                                  )
                                : const Icon(Icons.send),
                            label: Text(_submitting ? 'Mencatat...' : 'Catat Penyetoran'),
                            style: FilledButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              minimumSize: const Size(double.infinity, 48),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                if (ratings.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Text('Ulasan Petani', style: AppTextStyles.titleMedium),
                  const SizedBox(height: 8),
                  ...ratings.take(5).map((r) {
                    final uname = (r['user'] as Map?)?['name'] ?? 'Petani';
                    final rating = r['rating'] as int? ?? 0;
                    final ulasan = r['ulasan'] as String?;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    uname,
                                    style: AppTextStyles.labelLarge,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                const SizedBox(width: AppSpacing.sm),
                                Text(
                                  '${'★' * rating}${'☆' * (5 - rating)}',
                                  style: const TextStyle(color: AppColors.warning),
                                ),
                              ],
                            ),
                            if (ulasan != null && ulasan.isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text(ulasan, style: AppTextStyles.bodySmall),
                            ],
                          ],
                        ),
                      ),
                    );
                  }),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
