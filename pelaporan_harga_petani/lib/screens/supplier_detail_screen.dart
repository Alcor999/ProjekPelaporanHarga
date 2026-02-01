import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../core/theme/app_theme.dart';
import '../providers/supplier_provider.dart';
import '../providers/penyetoran_provider.dart';

class SupplierDetailScreen extends StatefulWidget {
  final int supplierId;

  const SupplierDetailScreen({Key? key, required this.supplierId}) : super(key: key);

  @override
  State<SupplierDetailScreen> createState() => _SupplierDetailScreenState();
}

class _SupplierDetailScreenState extends State<SupplierDetailScreen> {
  int? _selectedKomoditasId;
  final _jumlahController = TextEditingController();
  final _catatanController = TextEditingController();
  DateTime _selectedDate = DateTime.now();
  bool _submitting = false;
  int _ratingValue = 0;
  final _ulasanController = TextEditingController();

  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<SupplierProvider>().loadSupplierDetail(widget.supplierId));
  }

  @override
  void dispose() {
    _jumlahController.dispose();
    _catatanController.dispose();
    _ulasanController.dispose();
    super.dispose();
  }

  Future<void> _submitPenyetoran() async {
    if (_selectedKomoditasId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih komoditas terlebih dahulu')));
      return;
    }
    final jumlah = double.tryParse(_jumlahController.text);
    if (jumlah == null || jumlah <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Masukkan jumlah yang valid')));
      return;
    }
    setState(() => _submitting = true);
    final success = await context.read<PenyetoranProvider>().createPenyetoran(
      supplierId: widget.supplierId,
      komoditasId: _selectedKomoditasId!,
      jumlah: jumlah,
      tanggal: DateFormat('yyyy-MM-dd').format(_selectedDate),
      catatan: _catatanController.text.isEmpty ? null : _catatanController.text,
    );
    setState(() => _submitting = false);
    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Penyetoran berhasil dicatat'), backgroundColor: AppColors.success));
        _jumlahController.clear();
        _catatanController.clear();
        _showRatingModal();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal mencatat penyetoran'), backgroundColor: AppColors.error));
      }
    }
  }

  void _showRatingModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          return Container(
            padding: const EdgeInsets.all(AppSpacing.lg),
            decoration: const BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(bottom: 16),
                    decoration: BoxDecoration(color: AppColors.grey400, borderRadius: BorderRadius.circular(2)),
                  ),
                ),
                Text('Beri rating untuk supplier ini', style: AppTextStyles.titleMedium),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(5, (i) {
                    final star = i + 1;
                    return IconButton(
                      onPressed: () => setModalState(() => _ratingValue = star),
                      icon: Icon(star <= _ratingValue ? Icons.star : Icons.star_border, color: AppColors.warning, size: 36),
                    );
                  }),
                ),
                TextField(
                  controller: _ulasanController,
                  decoration: const InputDecoration(labelText: 'Ulasan (opsional)', hintText: 'Tulis pengalaman Anda...'),
                  maxLines: 3,
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    onPressed: () async {
                      if (_ratingValue < 1) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih rating 1-5')));
                        return;
                      }
                      final success = await context.read<PenyetoranProvider>().submitRating(widget.supplierId, rating: _ratingValue, ulasan: _ulasanController.text.isEmpty ? null : _ulasanController.text);
                      if (mounted) {
                        Navigator.pop(ctx);
                        if (success) {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Terima kasih atas rating Anda!'), backgroundColor: AppColors.success));
                          context.read<SupplierProvider>().loadSupplierDetail(widget.supplierId);
                        }
                      }
                    },
                    style: FilledButton.styleFrom(backgroundColor: AppColors.primary),
                    child: const Text('Kirim Rating'),
                  ),
                ),
                TextButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: const Text('Lewati'),
                ),
              ],
            ),
          );
        },
      ),
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
        title: const Text('Detail Supplier', style: TextStyle(color: AppColors.white, fontWeight: FontWeight.bold)),
      ),
      body: Consumer<SupplierProvider>(
        builder: (context, sp, _) {
          if (sp.loading && sp.supplierDetail == null) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
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
                          Row(children: [Icon(Icons.phone, size: 18, color: AppColors.grey600), const SizedBox(width: 8), Text(phone, style: AppTextStyles.bodyMedium)]),
                        ],
                        if (address != null && address.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Row(children: [Icon(Icons.location_on, size: 18, color: AppColors.grey600), const SizedBox(width: 8), Expanded(child: Text(address, style: AppTextStyles.bodySmall))]),
                        ],
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text('Harga per Komoditas', style: AppTextStyles.titleMedium),
                const SizedBox(height: 8),
                ...harga.map((h) {
                  final komo = h['komoditas'] as Map?;
                  final komoName = komo?['name'] ?? '?';
                  final grade = komo?['grade'];
                  final hrga = (h['harga_per_satuan'] as num?)?.toDouble() ?? 0;
                  final satuan = h['satuan'] as String? ?? 'kg';
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      title: Text('$komoName ${grade != null ? '($grade)' : ''}'),
                      subtitle: Text('Rp ${hrga.toStringAsFixed(0)}/$satuan', style: TextStyle(color: AppColors.success, fontWeight: FontWeight.bold)),
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
                          Text(namaHari, style: AppTextStyles.bodyMedium),
                          Text(isBuka ? jamRange : 'Tutup', style: AppTextStyles.bodyMedium.copyWith(color: isBuka ? AppColors.success : AppColors.grey500)),
                        ],
                      ),
                    );
                  }),
                ],
                const SizedBox(height: 24),
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
                        value: _selectedKomoditasId,
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
                          return DropdownMenuItem(value: kid, child: Text('$komoName ${grade != null ? "($grade)" : ""}'));
                        }).toList(),
                        onChanged: (v) => setState(() => _selectedKomoditasId = v),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _jumlahController,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: const InputDecoration(labelText: 'Jumlah', hintText: '0', border: OutlineInputBorder(), filled: true, fillColor: Colors.white),
                      ),
                      const SizedBox(height: 12),
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        title: const Text('Tanggal'),
                        subtitle: Text(DateFormat('dd/MM/yyyy').format(_selectedDate)),
                        trailing: IconButton(
                          icon: const Icon(Icons.calendar_today),
                          onPressed: () async {
                            final d = await showDatePicker(context: context, initialDate: _selectedDate, firstDate: DateTime(2020), lastDate: DateTime.now());
                            if (d != null) setState(() => _selectedDate = d);
                          },
                        ),
                      ),
                      TextField(
                        controller: _catatanController,
                        decoration: const InputDecoration(labelText: 'Catatan (opsional)', border: OutlineInputBorder(), filled: true, fillColor: Colors.white),
                        maxLines: 2,
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: _submitting ? null : _submitPenyetoran,
                          icon: _submitting ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.send),
                          label: Text(_submitting ? 'Mencatat...' : 'Catat Penyetoran'),
                          style: FilledButton.styleFrom(backgroundColor: AppColors.primary, padding: const EdgeInsets.symmetric(vertical: 12)),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                TextButton.icon(
                  onPressed: _showRatingModal,
                  icon: const Icon(Icons.star_border),
                  label: const Text('Beri Rating / Ulasan'),
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
                                Text(uname, style: AppTextStyles.labelLarge),
                                const Spacer(),
                                Text('${'★' * rating}${'☆' * (5 - rating)}', style: const TextStyle(color: AppColors.warning)),
                              ],
                            ),
                            if (ulasan != null && ulasan.isNotEmpty) ...[const SizedBox(height: 4), Text(ulasan, style: AppTextStyles.bodySmall)],
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
