import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/constants/api_constants.dart';
import '../core/theme/app_theme.dart';
import '../models/laporan_harga_model.dart';
import '../providers/laporan_provider.dart';
import '../widgets/common_widgets.dart';
import 'create_laporan_screen.dart';

class LaporanDetailScreen extends StatelessWidget {
  final LaporanHarga laporan;

  const LaporanDetailScreen({
    super.key,
    required this.laporan,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.white),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Detail Laporan',
          style: TextStyle(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          if (laporan.status == 'pending')
            PopupMenuButton(
              onSelected: (value) {
                if (value == 'edit') {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(
                      builder: (_) => CreateLaporanScreen(
                        editingLaporan: laporan,
                      ),
                    ),
                  );
                } else if (value == 'delete') {
                  _showDeleteConfirmation(context);
                }
              },
              itemBuilder: (BuildContext context) => <PopupMenuEntry<String>>[
                const PopupMenuItem(
                  value: 'edit',
                  child: Row(
                    children: [
                      Icon(Icons.edit, color: AppColors.primary),
                      SizedBox(width: AppSpacing.md),
                      Text('Edit'),
                    ],
                  ),
                ),
                const PopupMenuDivider(height: 8.0),
                const PopupMenuItem(
                  value: 'delete',
                  child: Row(
                    children: [
                      Icon(Icons.delete, color: AppColors.error),
                      SizedBox(width: AppSpacing.md),
                      Text('Hapus'),
                    ],
                  ),
                ),
              ],
            )
          else
            const SizedBox(),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Status Card
            Container(
              width: double.infinity,
              color: _getStatusBadgeBackground(laporan.status).withValues(alpha: 0.12),
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          'Status Laporan',
                          style: AppTextStyles.labelLarge.copyWith(
                            color: AppColors.grey600,
                          ),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.sm),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppSpacing.md,
                          vertical: AppSpacing.sm,
                        ),
                        decoration: BoxDecoration(
                          color: _getStatusBadgeBackground(laporan.status),
                          borderRadius: BorderRadius.circular(AppBorderRadius.sm),
                        ),
                        child: Text(
                          laporan.statusLabel,
                          style: AppTextStyles.labelSmall.copyWith(
                            color: _getStatusBadgeForeground(laporan.status),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(
                    laporan.formattedHarga,
                    style: AppTextStyles.headlineMedium.copyWith(
                      color: AppColors.primary,
                    ),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  Text(
                    'Harga per ${laporan.satuan}',
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.grey600,
                    ),
                  ),
                ],
              ),
            ),

            // Details
            Padding(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Informasi Laporan',
                    style: AppTextStyles.titleLarge,
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Komoditas
                  _buildDetailItem(
                    icon: Icons.grain,
                    label: 'Komoditas',
                    value: laporan.komoditas?['grade'] != null &&
                            laporan.komoditas!['grade'].toString().isNotEmpty
                        ? '${laporan.komoditas?['name'] ?? '-'} - ${laporan.komoditas?['grade']}'
                        : '${laporan.komoditas?['name'] ?? '-'}',
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Wilayah
                  _buildDetailItem(
                    icon: Icons.location_on,
                    label: 'Wilayah',
                    value: laporan.wilayah?['desa'] ?? '-',
                    subtitle:
                        '${laporan.wilayah?['kecamatan'] ?? ''}, ${laporan.wilayah?['kabupaten'] ?? ''}',
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Tanggal Panen
                  _buildDetailItem(
                    icon: Icons.calendar_today,
                    label: 'Tanggal Panen',
                    value: laporan.formattedTanggalPanen,
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Tanggal Laporan
                  if (laporan.tanggalLaporan != null)
                    Column(
                      children: [
                        _buildDetailItem(
                          icon: Icons.event_available,
                          label: 'Tanggal Laporan',
                          value: DateFormat('dd MMMM yyyy', 'id_ID')
                              .format(laporan.tanggalLaporan!),
                        ),
                        const SizedBox(height: AppSpacing.lg),
                      ],
                    ),

                  // Alasan Ditolak
                  if (laporan.status == 'rejected' &&
                      laporan.rejectionReason != null &&
                      laporan.rejectionReason!.isNotEmpty)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Alasan Ditolak', style: AppTextStyles.labelLarge.copyWith(color: AppColors.error)),
                        const SizedBox(height: AppSpacing.sm),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(AppSpacing.md),
                          decoration: BoxDecoration(
                            color: AppColors.error.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(AppBorderRadius.md),
                          ),
                          child: Text(laporan.rejectionReason!, style: AppTextStyles.bodyMedium),
                        ),
                        const SizedBox(height: AppSpacing.lg),
                      ],
                    ),

                  // Foto Bukti
                  if (laporan.fotoPath != null && laporan.fotoPath!.isNotEmpty)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Foto Bukti', style: AppTextStyles.labelLarge),
                        const SizedBox(height: AppSpacing.sm),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(AppBorderRadius.md),
                          child: CachedNetworkImage(
                            imageUrl: '${ApiConstants.storageUrl}/storage/${laporan.fotoPath}',
                            width: double.infinity,
                            fit: BoxFit.cover,
                            placeholder: (_, __) => const SizedBox(height: 150, child: Center(child: CircularProgressIndicator())),
                            errorWidget: (_, __, ___) => const Icon(Icons.broken_image, size: 48),
                          ),
                        ),
                        const SizedBox(height: AppSpacing.lg),
                      ],
                    ),

                  // Catatan
                  if (laporan.catatan != null && laporan.catatan!.isNotEmpty)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Catatan',
                          style: AppTextStyles.labelLarge,
                        ),
                        const SizedBox(height: AppSpacing.sm),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(AppSpacing.md),
                          decoration: BoxDecoration(
                            color: AppColors.grey100,
                            borderRadius:
                                BorderRadius.circular(AppBorderRadius.md),
                          ),
                          child: Text(
                            laporan.catatan!,
                            style: AppTextStyles.bodyMedium,
                          ),
                        ),
                        const SizedBox(height: AppSpacing.lg),
                      ],
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailItem({
    required IconData icon,
    required String label,
    required String value,
    String? subtitle,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, color: AppColors.primary, size: 24),
        SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: AppTextStyles.labelSmall.copyWith(
                  color: AppColors.grey600,
                ),
              ),
              SizedBox(height: AppSpacing.xs),
              Text(
                value,
                style: AppTextStyles.bodyMedium.copyWith(
                  fontWeight: FontWeight.w500,
                ),
              ),
              if (subtitle != null) ...[
                SizedBox(height: AppSpacing.xs),
                Text(
                  subtitle,
                  style: AppTextStyles.bodySmall.copyWith(
                    color: AppColors.grey600,
                  ),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }

  Color _getStatusBadgeBackground(String status) {
    switch (status) {
      case 'approved':
        return AppColors.success;
      case 'rejected':
        return AppColors.error;
      case 'pending':
        return AppColors.warningSurface;
      default:
        return AppColors.grey600;
    }
  }

  Color _getStatusBadgeForeground(String status) {
    return status == 'pending' ? AppColors.warningInk : AppColors.white;
  }

  void _showDeleteConfirmation(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(children: [Icon(Icons.warning_amber_rounded, color: AppColors.warning), SizedBox(width: 8), Text('Hapus Laporan')]),
        content: const Text(
          'Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          Consumer<LaporanProvider>(
            builder: (context, laporanProvider, _) {
              return FilledButton(
                onPressed: () async {
                  Navigator.pop(ctx);
                  final success =
                      await laporanProvider.deleteLaporan(laporan.id ?? 0);
                  if (success && context.mounted) {
                    showSuccessSnackBar(context, 'Laporan berhasil dihapus');
                    Navigator.of(context).popUntil(
                      (route) => route.isFirst,
                    );
                  } else if (!success && context.mounted) {
                    showErrorSnackBar(
                      context,
                      laporanProvider.errorMessage ?? 'Gagal menghapus laporan',
                    );
                  }
                },
                style: FilledButton.styleFrom(backgroundColor: AppColors.error),
                child: const Text('Hapus'),
              );
            },
          ),
        ],
      ),
    );
  }
}

