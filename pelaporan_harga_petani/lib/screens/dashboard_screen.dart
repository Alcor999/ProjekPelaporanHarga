import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme/app_theme.dart';
import '../models/laporan_harga_model.dart';
import '../providers/auth_provider.dart';
import '../providers/laporan_provider.dart';
import '../widgets/common_widgets.dart';
import 'create_laporan_screen.dart';
import 'laporan_detail_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<LaporanProvider>().loadLaporanList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        title: const Text(
          'Laporan Harga Padi',
          style: TextStyle(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Center(
              child: Consumer<AuthProvider>(
                builder: (context, authProvider, _) {
                  return PopupMenuButton(
                    onSelected: (value) {
                      if (value == 'logout') {
                        _showLogoutDialog();
                      } else if (value == 'profile') {
                        _showProfileDialog();
                      }
                    },
                    itemBuilder: (BuildContext context) => <PopupMenuEntry<String>>[
                      const PopupMenuItem(
                        value: 'profile',
                        child: Row(
                          children: [
                            Icon(Icons.person, color: AppColors.primary),
                            SizedBox(width: AppSpacing.md),
                            Text('Profil'),
                          ],
                        ),
                      ),
                      const PopupMenuDivider(height: 8.0),
                      const PopupMenuItem(
                        value: 'logout',
                        child: Row(
                          children: [
                            Icon(Icons.logout, color: AppColors.error),
                            SizedBox(width: AppSpacing.md),
                            Text('Logout'),
                          ],
                        ),
                      ),
                    ],
                  );
                },
              ),
            ),
          ),
        ],
      ),
      body: Consumer<LaporanProvider>(
        builder: (context, laporanProvider, _) {
          if (laporanProvider.isLoading) {
            return Center(
              child: CircularProgressIndicator(
                color: AppColors.primary,
              ),
            );
          }

          if (laporanProvider.errorMessage != null) {
            return ErrorWidget2(
              message: laporanProvider.errorMessage ?? 'Terjadi kesalahan',
              onRetry: () {
                context.read<LaporanProvider>().loadLaporanList();
              },
            );
          }

          if (laporanProvider.laporanList.isEmpty) {
            return EmptyStateWidget(
              title: 'Belum Ada Laporan',
              message: 'Mulai buat laporan harga padi Anda sekarang',
              icon: Icons.create_outlined,
              onRetry: () {
                context.read<LaporanProvider>().loadLaporanList();
              },
            );
          }

          return RefreshIndicator(
            onRefresh: () {
              return context.read<LaporanProvider>().loadLaporanList();
            },
            color: AppColors.primary,
            child: ListView.builder(
              padding: const EdgeInsets.all(AppSpacing.md),
              itemCount: laporanProvider.laporanList.length,
              itemBuilder: (context, index) {
                final laporan = laporanProvider.laporanList[index];
                return _buildLaporanCard(context, laporan);
              },
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => const CreateLaporanScreen(),
            ),
          );
        },
        backgroundColor: AppColors.primary,
        child: const Icon(Icons.add, color: AppColors.white),
      ),
    );
  }

  Widget _buildLaporanCard(BuildContext context, LaporanHarga laporan) {
    return GestureDetector(
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => LaporanDetailScreen(laporan: laporan),
          ),
        );
      },
      child: Card(
        margin: const EdgeInsets.only(bottom: AppSpacing.md),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppBorderRadius.md),
        ),
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.md),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          laporan.komoditas?['name'] ?? 'Padi',
                          style: AppTextStyles.titleMedium,
                        ),
                        SizedBox(height: AppSpacing.xs),
                        Text(
                          laporan.komoditas?['grade'] ?? 'Grade',
                          style: AppTextStyles.bodySmall.copyWith(
                            color: AppColors.grey600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSpacing.md,
                      vertical: AppSpacing.sm,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor(laporan.status).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(AppBorderRadius.sm),
                    ),
                    child: Text(
                      laporan.statusLabel,
                      style: AppTextStyles.labelSmall.copyWith(
                        color: _getStatusColor(laporan.status),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              SizedBox(height: AppSpacing.md),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Harga',
                        style: AppTextStyles.bodySmall.copyWith(
                          color: AppColors.grey600,
                        ),
                      ),
                      SizedBox(height: AppSpacing.xs),
                      Text(
                        laporan.formattedHarga,
                        style: AppTextStyles.titleMedium.copyWith(
                          color: AppColors.primary,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Tanggal Panen',
                        style: AppTextStyles.bodySmall.copyWith(
                          color: AppColors.grey600,
                        ),
                      ),
                      SizedBox(height: AppSpacing.xs),
                      Text(
                        laporan.formattedTanggalPanen,
                        style: AppTextStyles.bodyMedium.copyWith(
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              SizedBox(height: AppSpacing.md),
              Divider(color: AppColors.grey200),
              SizedBox(height: AppSpacing.sm),
              Text(
                laporan.wilayah?['desa'] ?? 'Lokasi',
                style: AppTextStyles.bodySmall.copyWith(
                  color: AppColors.grey600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'approved':
        return AppColors.success;
      case 'rejected':
        return AppColors.error;
      case 'pending':
        return AppColors.warning;
      default:
        return AppColors.grey500;
    }
  }

  void _showProfileDialog() {
    final authProvider = context.read<AuthProvider>();
    final user = authProvider.user;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Profil Anda'),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildProfileItem('Nama', user?.name ?? '-'),
              SizedBox(height: AppSpacing.md),
              _buildProfileItem('Email', user?.email ?? '-'),
              SizedBox(height: AppSpacing.md),
              _buildProfileItem('Telepon', user?.phone ?? '-'),
              SizedBox(height: AppSpacing.md),
              _buildProfileItem('Alamat', user?.address ?? '-'),
              SizedBox(height: AppSpacing.md),
              _buildProfileItem('Status', user?.status ?? '-'),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileItem(String label, String value) {
    return Column(
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
          style: AppTextStyles.bodyMedium,
        ),
      ],
    );
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Apakah Anda yakin ingin logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () async {
              Navigator.of(context).pop();
              final authProvider = context.read<AuthProvider>();
              await authProvider.logout();
              if (mounted) {
                Navigator.of(context).pushNamedAndRemoveUntil(
                  '/login',
                  (route) => false,
                );
              }
            },
            child: const Text(
              'Logout',
              style: TextStyle(color: AppColors.error),
            ),
          ),
        ],
      ),
    );
  }
}

