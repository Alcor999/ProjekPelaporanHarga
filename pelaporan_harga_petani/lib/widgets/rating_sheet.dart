import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme/app_theme.dart';
import '../providers/penyetoran_provider.dart';

Future<void> showSupplierRatingSheet(
  BuildContext context, {
  required int supplierId,
  required int penyetoranId,
  VoidCallback? onSubmitted,
}) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) => _SupplierRatingSheetContent(
      supplierId: supplierId,
      penyetoranId: penyetoranId,
      onSubmitted: onSubmitted,
    ),
  );
}

class _SupplierRatingSheetContent extends StatefulWidget {
  final int supplierId;
  final int penyetoranId;
  final VoidCallback? onSubmitted;

  const _SupplierRatingSheetContent({
    required this.supplierId,
    required this.penyetoranId,
    this.onSubmitted,
  });

  @override
  State<_SupplierRatingSheetContent> createState() =>
      _SupplierRatingSheetContentState();
}

class _SupplierRatingSheetContentState
    extends State<_SupplierRatingSheetContent> {
  int _ratingValue = 0;
  bool _submitting = false;
  final _ulasanController = TextEditingController();

  @override
  void dispose() {
    _ulasanController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_ratingValue < 1) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih rating 1 sampai 5 bintang')),
      );
      return;
    }
    setState(() => _submitting = true);
    final pp = context.read<PenyetoranProvider>();
    final success = await pp.submitRating(
      widget.supplierId,
      rating: _ratingValue,
      ulasan: _ulasanController.text.trim().isEmpty
          ? null
          : _ulasanController.text.trim(),
      penyetoranId: widget.penyetoranId,
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Terima kasih atas rating Anda!'),
          backgroundColor: AppColors.success,
        ),
      );
      widget.onSubmitted?.call();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(pp.error ?? 'Gagal menyimpan rating'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        child: Container(
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
                  decoration: BoxDecoration(
                    color: AppColors.grey400,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Text('Beri rating untuk supplier ini', style: AppTextStyles.titleMedium),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(5, (i) {
                  final star = i + 1;
                  return IconButton(
                    tooltip: '$star bintang',
                    constraints: const BoxConstraints(minWidth: 48, minHeight: 48),
                    onPressed: _submitting
                        ? null
                        : () => setState(() => _ratingValue = star),
                    icon: Icon(
                      star <= _ratingValue ? Icons.star : Icons.star_border,
                      color: AppColors.warning,
                      size: 36,
                    ),
                  );
                }),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _ulasanController,
                enabled: !_submitting,
                decoration: const InputDecoration(
                  labelText: 'Ulasan (opsional)',
                  hintText: 'Tulis pengalaman Anda...',
                ),
                maxLines: 3,
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: _submitting ? null : _submit,
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    minimumSize: const Size(double.infinity, 48),
                  ),
                  child: Text(_submitting ? 'Mengirim...' : 'Kirim Rating'),
                ),
              ),
              Center(
                child: TextButton(
                  onPressed: _submitting ? null : () => Navigator.pop(context),
                  child: const Text('Lewati'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
