import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme/app_theme.dart';
import '../models/laporan_harga_model.dart';
import '../providers/auth_provider.dart';
import '../providers/laporan_provider.dart';
import '../providers/notification_provider.dart';
import '../widgets/common_widgets.dart';
import 'create_laporan_screen.dart';
import 'laporan_detail_screen.dart';
import 'harga_publik_screen.dart';
import 'notifications_screen.dart';
import 'supplier_list_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  String? _filterStatus;
  int? _filterWilayah;
  int? _filterKomoditas;
  final _searchController = TextEditingController();
  Timer? _searchDebounce;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<LaporanProvider>().loadLaporanList();
      context.read<LaporanProvider>().initReferenceData();
      context.read<NotificationProvider>().refreshUnreadCount();
    });
    _searchController.addListener(_onSearchChanged);
  }

  void _onSearchChanged() {
    _searchDebounce?.cancel();
    _searchDebounce = Timer(const Duration(milliseconds: 400), () {
      if (mounted) _applyFilter();
    });
  }

  @override
  void dispose() {
    _searchDebounce?.cancel();
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _applyFilter() {
    context.read<LaporanProvider>().loadLaporanList(
      status: _filterStatus,
      wilayahId: _filterWilayah,
      komoditasId: _filterKomoditas,
      search: _searchController.text.isEmpty ? null : _searchController.text,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        title: const Text(
          'Laporan Harga Komoditas',
          style: TextStyle(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        actions: [
          IconButton(
            icon: Consumer<NotificationProvider>(
              builder: (context, np, _) => Badge(
                isLabelVisible: np.unreadCount > 0,
                label: Text('${np.unreadCount}'),
                child: const Icon(Icons.notifications, color: AppColors.white),
              ),
            ),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.trending_up, color: AppColors.white),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HargaPublikScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.store, color: AppColors.white),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SupplierListScreen())),
          ),
          Padding(
            padding: const EdgeInsets.all(AppSpacing.sm),
            child: Center(
              child: Consumer<AuthProvider>(
                builder: (context, authProvider, _) {
                  return PopupMenuButton(
                    onSelected: (value) {
                      if (value == 'logout') _showLogoutDialog();
                      else if (value == 'profile') _showProfileDialog();
                      else if (value == 'harga') Navigator.push(context, MaterialPageRoute(builder: (_) => const HargaPublikScreen()));
                      else if (value == 'supplier') Navigator.push(context, MaterialPageRoute(builder: (_) => const SupplierListScreen()));
                    },
                    itemBuilder: (BuildContext context) => <PopupMenuEntry<String>>[
                      const PopupMenuItem(
                        value: 'harga',
                        child: Row(
                          children: [
                            Icon(Icons.trending_up, color: AppColors.primary),
                            SizedBox(width: AppSpacing.md),
                            Text('Harga Pasar'),
                          ],
                        ),
                      ),
                      const PopupMenuItem(
                        value: 'supplier',
                        child: Row(
                          children: [
                            Icon(Icons.store, color: AppColors.primary),
                            SizedBox(width: AppSpacing.md),
                            Text('Setor Hasil Panen'),
                          ],
                        ),
                      ),
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
              message: 'Mulai buat laporan harga komoditas Anda sekarang',
              icon: Icons.create_outlined,
              onRetry: () {
                context.read<LaporanProvider>().loadLaporanList();
              },
            );
          }

          return Column(
            children: [
              Container(
                color: AppColors.grey100,
                padding: const EdgeInsets.all(AppSpacing.md),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    TextField(
                      controller: _searchController,
                      decoration: InputDecoration(
                        hintText: 'Cari komoditas atau catatan...',
                        prefixIcon: const Icon(Icons.search, color: AppColors.primary),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: AppColors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 8),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          DropdownButton<int?>(
                            value: _filterWilayah,
                            hint: const Text('Wilayah'),
                            items: [
                              const DropdownMenuItem(value: null, child: Text('Semua Wilayah')),
                              ...laporanProvider.wilayahList.map((w) => DropdownMenuItem(value: w.id, child: Text(w.displayName, overflow: TextOverflow.ellipsis))),
                            ],
                            onChanged: (v) {
                              setState(() => _filterWilayah = v);
                              _applyFilter();
                            },
                          ),
                          const SizedBox(width: 8),
                          DropdownButton<int?>(
                            value: _filterKomoditas,
                            hint: const Text('Komoditas'),
                            items: [
                              const DropdownMenuItem(value: null, child: Text('Semua')),
                              ...laporanProvider.komoditasList.map((k) => DropdownMenuItem(value: k.id, child: Text(k.displayName, overflow: TextOverflow.ellipsis))),
                            ],
                            onChanged: (v) {
                              setState(() => _filterKomoditas = v);
                              _applyFilter();
                            },
                          ),
                          const SizedBox(width: 8),
                          DropdownButton<String?>(
                            value: _filterStatus,
                            hint: const Text('Status'),
                            items: const [
                              DropdownMenuItem(value: null, child: Text('Semua')),
                              DropdownMenuItem(value: 'pending', child: Text('Pending')),
                              DropdownMenuItem(value: 'approved', child: Text('Disetujui')),
                              DropdownMenuItem(value: 'rejected', child: Text('Ditolak')),
                            ],
                            onChanged: (v) {
                              setState(() => _filterStatus = v);
                              _applyFilter();
                            },
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: RefreshIndicator(
                  onRefresh: () => context.read<LaporanProvider>().loadLaporanList(
                    status: _filterStatus,
                    wilayahId: _filterWilayah,
                    komoditasId: _filterKomoditas,
                    search: _searchController.text.isEmpty ? null : _searchController.text,
                  ),
            color: AppColors.primary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    itemCount: laporanProvider.laporanList.length,
                    itemBuilder: (context, index) {
                final laporan = laporanProvider.laporanList[index];
                      return _buildLaporanCard(context, laporan);
                    },
                  ),
                ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          showModalBottomSheet(
            context: context,
            isScrollControlled: true,
            backgroundColor: Colors.transparent,
            builder: (ctx) => DraggableScrollableSheet(
              initialChildSize: 0.95,
              minChildSize: 0.5,
              maxChildSize: 1,
              builder: (_, __) => Container(
                decoration: const BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                ),
                child: Column(
                  children: [
                    Container(
                      margin: const EdgeInsets.only(top: 12, bottom: 8),
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: AppColors.grey400,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                    const Expanded(child: CreateLaporanScreen(isModal: true)),
                  ],
                ),
              ),
            ),
          );
        },
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: AppColors.white),
        label: const Text('Buat Laporan', style: TextStyle(color: AppColors.white, fontWeight: FontWeight.bold)),
      ),
    );
  }

  void _showLaporanModal(BuildContext context, LaporanHarga laporan) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        minChildSize: 0.4,
        maxChildSize: 0.9,
        builder: (_, controller) => Container(
          decoration: const BoxDecoration(
            color: AppColors.background,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.grey400,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Expanded(
                child: ListView(
                  controller: controller,
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(laporan.komoditas?['name'] ?? 'Padi', style: AppTextStyles.titleLarge),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: _getStatusColor(laporan.status).withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(laporan.statusLabel, style: TextStyle(color: _getStatusColor(laporan.status), fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    _buildModalRow('Harga', laporan.formattedHarga),
                    _buildModalRow('Wilayah', '${laporan.wilayah?['desa'] ?? ''}, ${laporan.wilayah?['kecamatan'] ?? ''}'),
                    _buildModalRow('Tanggal Panen', laporan.formattedTanggalPanen),
                    if (laporan.catatan != null && laporan.catatan!.isNotEmpty) _buildModalRow('Catatan', laporan.catatan!),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.pop(ctx);
                          Navigator.push(ctx, MaterialPageRoute(builder: (_) => LaporanDetailScreen(laporan: laporan)));
                        },
                        icon: const Icon(Icons.open_in_new),
                        label: const Text('Lihat Detail Lengkap'),
                        style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 12)),
                      ),
                    ),
                    if (laporan.status == 'pending') ...[
                      const SizedBox(height: 8),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () {
                            Navigator.pop(ctx);
                            Navigator.push(ctx, MaterialPageRoute(builder: (_) => CreateLaporanScreen(editingLaporan: laporan)));
                          },
                          icon: const Icon(Icons.edit),
                          label: const Text('Edit Laporan'),
                          style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 12)),
                        ),
                      ),
                      const SizedBox(height: 8),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () {
                            Navigator.pop(ctx);
                            _showDeleteDialog(context, laporan);
                          },
                          icon: const Icon(Icons.delete, color: AppColors.error),
                          label: const Text('Hapus', style: TextStyle(color: AppColors.error)),
                          style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, padding: const EdgeInsets.symmetric(vertical: 12)),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildModalRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: AppTextStyles.bodySmall.copyWith(color: AppColors.grey600)),
          const SizedBox(height: 4),
          Text(value, style: AppTextStyles.bodyMedium),
        ],
      ),
    );
  }

  void _showDeleteDialog(BuildContext context, LaporanHarga laporan) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(children: [Icon(Icons.warning_amber_rounded, color: AppColors.warning), SizedBox(width: 8), Text('Hapus Laporan')]),
        content: const Text('Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          FilledButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await context.read<LaporanProvider>().deleteLaporan(laporan.id ?? 0);
              if (context.mounted) {
                if (success) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Laporan berhasil dihapus'), backgroundColor: AppColors.success));
                }
              }
            },
            style: FilledButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
  }

  Widget _buildLaporanCard(BuildContext context, LaporanHarga laporan) {
    return GestureDetector(
      onTap: () => _showLaporanModal(context, laporan),
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

