import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme/app_theme.dart';
import '../models/laporan_harga_model.dart';
import '../providers/auth_provider.dart';
import '../providers/laporan_provider.dart';
import '../providers/notification_provider.dart';
import '../providers/penyetoran_provider.dart';
import '../widgets/common_widgets.dart';
import 'create_laporan_screen.dart';
import 'laporan_detail_screen.dart';
import 'harga_publik_screen.dart';
import 'notifications_screen.dart';
import 'supplier_list_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  String? _filterStatus;
  int? _filterWilayah;
  int? _filterKomoditas;
  final _searchController = TextEditingController();
  Timer? _searchDebounce;

  bool get _hasActiveFilter =>
      _filterStatus != null ||
      _filterWilayah != null ||
      _filterKomoditas != null ||
      _searchController.text.trim().isNotEmpty;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (!mounted) return;
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

  Future<void> _applyFilter() async {
    await context.read<LaporanProvider>().loadLaporanList(
      status: _filterStatus,
      wilayahId: _filterWilayah,
      komoditasId: _filterKomoditas,
      search: _searchController.text.trim().isEmpty
          ? null
          : _searchController.text.trim(),
    );
  }

  void _resetFilters() {
    _searchDebounce?.cancel();
    setState(() {
      _filterStatus = null;
      _filterWilayah = null;
      _filterKomoditas = null;
      _searchController.clear();
    });
    _applyFilter();
  }

  String _localizedAccountStatus(String? status) {
    switch (status) {
      case 'active':
        return 'Aktif';
      case 'pending':
        return 'Menunggu Persetujuan';
      case 'blocked':
        return 'Diblokir';
      default:
        return status ?? '-';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        title: const Text(
          'Lapor Harga Panen',
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Notifikasi',
            icon: Consumer<NotificationProvider>(
              builder: (context, np, _) => Badge(
                isLabelVisible: np.unreadCount > 0,
                label: Text('${np.unreadCount}'),
                child: const Icon(Icons.notifications, color: AppColors.white),
              ),
            ),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            ),
          ),
          PopupMenuButton<String>(
            iconColor: AppColors.white,
            tooltip: 'Menu akun',
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
          ),
        ],
      ),
      body: Column(
        children: [
          Consumer<AuthProvider>(
            builder: (context, authProvider, _) {
              if (!authProvider.isOfflineSession) {
                return const SizedBox.shrink();
              }
              return Container(
                width: double.infinity,
                color: AppColors.warningSurface,
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.md,
                  vertical: AppSpacing.sm,
                ),
                child: Row(
                  children: [
                    const Icon(Icons.cloud_off, color: AppColors.warningInk, size: 20),
                    const SizedBox(width: AppSpacing.sm),
                    const Expanded(
                      child: Text(
                        'Mode offline - menampilkan profil tersimpan',
                        style: TextStyle(
                          color: AppColors.warningInk,
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    TextButton(
                      onPressed: () async {
                        final ok = await authProvider.refreshProfile();
                        if (ok && context.mounted) {
                          _applyFilter();
                          context.read<LaporanProvider>().initReferenceData();
                          context.read<NotificationProvider>().refreshUnreadCount();
                        }
                      },
                      child: const Text(
                        'Coba Lagi',
                        style: TextStyle(
                          color: AppColors.warningInk,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
          Container(
            color: AppColors.surface,
            padding: const EdgeInsets.fromLTRB(AppSpacing.md, AppSpacing.sm, AppSpacing.md, 0),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const HargaPublikScreen()),
                    ),
                    icon: const Icon(Icons.trending_up, size: 18),
                    label: const Text('Harga Pasar', overflow: TextOverflow.ellipsis),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(0, 44),
                      foregroundColor: AppColors.primaryDark,
                      side: const BorderSide(color: AppColors.grey300),
                    ),
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const SupplierListScreen()),
                    ),
                    icon: const Icon(Icons.store, size: 18),
                    label: const Text('Setor Panen', overflow: TextOverflow.ellipsis),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(0, 44),
                      foregroundColor: AppColors.primaryDark,
                      side: const BorderSide(color: AppColors.grey300),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Consumer<LaporanProvider>(
            builder: (context, laporanProvider, _) {
              return Container(
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
                        suffixIcon: _searchController.text.isNotEmpty
                            ? IconButton(
                                tooltip: 'Hapus pencarian',
                                icon: const Icon(Icons.clear),
                                onPressed: () {
                                  _searchController.clear();
                                  _applyFilter();
                                },
                              )
                            : null,
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
                              ...laporanProvider.wilayahList.map(
                                (w) => DropdownMenuItem(
                                  value: w.id,
                                  child: Text(w.displayName, overflow: TextOverflow.ellipsis),
                                ),
                              ),
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
                              const DropdownMenuItem(value: null, child: Text('Semua Komoditas')),
                              ...laporanProvider.komoditasList.map(
                                (k) => DropdownMenuItem(
                                  value: k.id,
                                  child: Text(k.displayName, overflow: TextOverflow.ellipsis),
                                ),
                              ),
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
                              DropdownMenuItem(value: null, child: Text('Semua Status')),
                              DropdownMenuItem(value: 'pending', child: Text('Menunggu')),
                              DropdownMenuItem(value: 'approved', child: Text('Disetujui')),
                              DropdownMenuItem(value: 'rejected', child: Text('Ditolak')),
                            ],
                            onChanged: (v) {
                              setState(() => _filterStatus = v);
                              _applyFilter();
                            },
                          ),
                          if (_hasActiveFilter) ...[
                            const SizedBox(width: 8),
                            TextButton.icon(
                              onPressed: _resetFilters,
                              icon: const Icon(Icons.filter_alt_off, size: 18),
                              label: const Text('Reset'),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
          Expanded(
            child: Consumer<LaporanProvider>(
              builder: (context, laporanProvider, _) {
                if (laporanProvider.isLoading && laporanProvider.laporanList.isEmpty) {
                  return const Center(
                    child: CircularProgressIndicator(color: AppColors.primary),
                  );
                }

                if (laporanProvider.errorMessage != null && laporanProvider.laporanList.isEmpty) {
                  return ErrorWidget2(
                    message: laporanProvider.errorMessage ?? 'Terjadi kesalahan',
                    onRetry: _applyFilter,
                  );
                }

                return Column(
                  children: [
                    if (laporanProvider.isLoading)
                      const LinearProgressIndicator(color: AppColors.primary),
                    Expanded(
                      child: RefreshIndicator(
                        onRefresh: _applyFilter,
                        color: AppColors.primary,
                        child: laporanProvider.laporanList.isEmpty
                            ? LayoutBuilder(
                                builder: (context, constraints) => SingleChildScrollView(
                                  physics: const AlwaysScrollableScrollPhysics(),
                                  child: ConstrainedBox(
                                    constraints: BoxConstraints(minHeight: constraints.maxHeight),
                                    child: EmptyStateWidget(
                                      title: _hasActiveFilter
                                          ? 'Tidak Ada Laporan Cocok'
                                          : 'Belum Ada Laporan',
                                      message: _hasActiveFilter
                                          ? 'Coba ubah atau reset filter pencarian Anda'
                                          : 'Mulai buat laporan harga komoditas Anda sekarang',
                                      icon: _hasActiveFilter
                                          ? Icons.search_off
                                          : Icons.create_outlined,
                                      onRetry: _hasActiveFilter ? _resetFilters : _applyFilter,
                                    ),
                                  ),
                                ),
                              )
                            : ListView.builder(
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
          ),
        ],
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

  void _showLaporanModal(BuildContext context, LaporanHarga laporan) {
    final rootNavigator = Navigator.of(context);
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
                        Expanded(
                          child: Text(
                            laporan.komoditas?['name'] ?? 'Komoditas',
                            style: AppTextStyles.titleLarge,
                          ),
                        ),
                        const SizedBox(width: AppSpacing.sm),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: _getStatusBadgeBackground(laporan.status),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            laporan.statusLabel,
                            style: TextStyle(
                              color: _getStatusBadgeForeground(laporan.status),
                              fontWeight: FontWeight.bold,
                            ),
                          ),
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
                          rootNavigator.push(
                            MaterialPageRoute(builder: (_) => LaporanDetailScreen(laporan: laporan)),
                          );
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
                            rootNavigator.push(
                              MaterialPageRoute(builder: (_) => CreateLaporanScreen(editingLaporan: laporan)),
                            );
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
              final provider = context.read<LaporanProvider>();
              final success = await provider.deleteLaporan(laporan.id ?? 0);
              if (context.mounted) {
                if (success) {
                  showSuccessSnackBar(context, 'Laporan berhasil dihapus');
                } else {
                  showErrorSnackBar(
                    context,
                    provider.errorMessage ?? 'Gagal menghapus laporan',
                  );
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
    final grade = laporan.komoditas?['grade']?.toString();
    return Card(
      margin: const EdgeInsets.only(bottom: AppSpacing.md),
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppBorderRadius.md),
      ),
      child: InkWell(
        onTap: () => _showLaporanModal(context, laporan),
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.md),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          laporan.komoditas?['name'] ?? 'Komoditas',
                          style: AppTextStyles.titleMedium,
                        ),
                        if (grade != null && grade.isNotEmpty) ...[
                          const SizedBox(height: AppSpacing.xs),
                          Text(
                            grade,
                            style: AppTextStyles.bodySmall.copyWith(
                              color: AppColors.grey600,
                            ),
                          ),
                        ],
                      ],
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
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Harga',
                          style: AppTextStyles.bodySmall.copyWith(
                            color: AppColors.grey600,
                          ),
                        ),
                        const SizedBox(height: AppSpacing.xs),
                        Text(
                          laporan.formattedHarga,
                          style: AppTextStyles.titleMedium.copyWith(
                            color: AppColors.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Tanggal Panen',
                        style: AppTextStyles.bodySmall.copyWith(
                          color: AppColors.grey600,
                        ),
                      ),
                      const SizedBox(height: AppSpacing.xs),
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
              const SizedBox(height: AppSpacing.md),
              const Divider(color: AppColors.grey200),
              const SizedBox(height: AppSpacing.sm),
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
              const SizedBox(height: AppSpacing.md),
              _buildProfileItem('Email', user?.email ?? '-'),
              const SizedBox(height: AppSpacing.md),
              _buildProfileItem('Telepon', user?.phone ?? '-'),
              const SizedBox(height: AppSpacing.md),
              _buildProfileItem('Alamat', user?.address ?? '-'),
              const SizedBox(height: AppSpacing.md),
              _buildProfileItem('Status', _localizedAccountStatus(user?.status)),
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
              final authProvider = this.context.read<AuthProvider>();
              this.context.read<LaporanProvider>().reset();
              this.context.read<NotificationProvider>().reset();
              this.context.read<PenyetoranProvider>().reset();
              await authProvider.logout();
              if (mounted) {
                Navigator.of(this.context).popUntil((route) => route.isFirst);
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

