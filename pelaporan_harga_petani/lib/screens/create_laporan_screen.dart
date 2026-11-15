import 'dart:convert';
import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import '../core/constants/api_constants.dart';
import '../core/theme/app_theme.dart';
import '../models/laporan_harga_model.dart';
import '../providers/laporan_provider.dart';
import '../widgets/buttons.dart';
import '../widgets/custom_text_field.dart';
import '../widgets/common_widgets.dart';

class CreateLaporanScreen extends StatefulWidget {
  final LaporanHarga? editingLaporan;
  final bool isModal;

  const CreateLaporanScreen({
    super.key,
    this.editingLaporan,
    this.isModal = false,
  });

  @override
  State<CreateLaporanScreen> createState() => _CreateLaporanScreenState();
}

class _CreateLaporanScreenState extends State<CreateLaporanScreen> {
  static const int _maxPhotoBytes = 2 * 1024 * 1024;

  late TextEditingController _hargaController;
  late TextEditingController _satuanController;
  late TextEditingController _catatanController;
  DateTime? _selectedTanggalPanen;
  int? _selectedWilayahId;
  int? _selectedKomoditasId;
  XFile? _selectedFoto;
  bool _removeExistingFoto = false;
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  final _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    final editing = widget.editingLaporan;
    final initialHarga = editing == null
        ? ''
        : (editing.harga == editing.harga.roundToDouble()
            ? editing.harga.toStringAsFixed(0)
            : editing.harga.toString());

    _hargaController = TextEditingController(text: initialHarga);
    _satuanController = TextEditingController(text: editing?.satuan ?? 'kg');
    _catatanController = TextEditingController(text: editing?.catatan ?? '');
    _selectedTanggalPanen = editing?.tanggalPanen ?? DateTime.now();
    _selectedWilayahId = editing?.wilayahId;
    _selectedKomoditasId = editing?.komoditasId;

    Future.microtask(() {
      if (!mounted) return;
      final laporanProvider = context.read<LaporanProvider>();
      if (laporanProvider.wilayahList.isEmpty || laporanProvider.komoditasList.isEmpty) {
        laporanProvider.initReferenceData();
      }
    });
  }

  @override
  void dispose() {
    _hargaController.dispose();
    _satuanController.dispose();
    _catatanController.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto() async {
    final x = await _picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1024,
      maxHeight: 1024,
      imageQuality: 80,
    );
    if (x == null || !mounted) return;

    final fileSize = await x.length();
    if (!mounted) return;
    if (fileSize > _maxPhotoBytes) {
      showErrorSnackBar(context, 'Ukuran foto maksimal 2 MB. Pilih gambar yang lebih kecil.');
      return;
    }

    setState(() {
      _selectedFoto = x;
      _removeExistingFoto = false;
    });
  }

  void _handleSubmit() async {
    if (_formKey.currentState?.validate() ?? false) {
      if (_selectedWilayahId == null) {
        showErrorSnackBar(context, 'Pilih wilayah terlebih dahulu');
        return;
      }
      if (_selectedKomoditasId == null) {
        showErrorSnackBar(context, 'Pilih komoditas terlebih dahulu');
        return;
      }

      final parsedHarga = double.parse(_hargaController.text.replaceAll(',', '.').trim());
      final laporan = LaporanHarga(
        id: widget.editingLaporan?.id,
        userId: widget.editingLaporan?.userId ?? 0,
        wilayahId: _selectedWilayahId!,
        komoditasId: _selectedKomoditasId!,
        harga: parsedHarga,
        satuan: _satuanController.text.trim().isEmpty ? 'kg' : _satuanController.text.trim(),
        tanggalPanen: _selectedTanggalPanen ?? DateTime.now(),
        catatan: _catatanController.text.trim().isEmpty ? null : _catatanController.text.trim(),
        status: widget.editingLaporan?.status ?? 'pending',
      );

      String? fotoBase64;
      if (_selectedFoto != null) {
        final bytes = await File(_selectedFoto!.path).readAsBytes();
        if (bytes.length > _maxPhotoBytes) {
          if (mounted) {
            showErrorSnackBar(context, 'Ukuran foto maksimal 2 MB.');
          }
          return;
        }
        fotoBase64 = 'data:image/jpeg;base64,${base64Encode(bytes)}';
      }

      if (!mounted) return;
      final laporanProvider = context.read<LaporanProvider>();
      bool success;

      if (widget.editingLaporan != null) {
        success = await laporanProvider.updateLaporan(
          widget.editingLaporan!.id!,
          laporan,
          fotoBase64: fotoBase64,
          removeFoto: _removeExistingFoto && _selectedFoto == null,
        );
      } else {
        success = await laporanProvider.createLaporan(laporan, fotoBase64: fotoBase64);
      }

      if (success && mounted) {
        showSuccessSnackBar(
          context,
          widget.editingLaporan != null
              ? 'Laporan berhasil diupdate'
              : 'Laporan berhasil dibuat',
        );
        Navigator.of(context).pop();
      } else if (!success && mounted) {
        showErrorSnackBar(
          context,
          laporanProvider.errorMessage ?? 'Gagal menyimpan laporan',
        );
      }
    }
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedTanggalPanen ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.primary,
              secondary: AppColors.primary,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _selectedTanggalPanen = picked;
      });
    }
  }

  Widget _buildReferenceErrorCard(String message, VoidCallback onRetry) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.error.withValues(alpha: 0.08),
        border: Border.all(color: AppColors.error.withValues(alpha: 0.4)),
        borderRadius: BorderRadius.circular(AppBorderRadius.md),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: AppColors.error),
          const SizedBox(width: AppSpacing.sm),
          Expanded(
            child: Text(
              message,
              style: AppTextStyles.bodySmall.copyWith(color: AppColors.error),
            ),
          ),
          TextButton(
            onPressed: onRetry,
            child: const Text('Muat Ulang'),
          ),
        ],
      ),
    );
  }

  Widget _buildFormContent(BuildContext context) {
    final existingFoto = widget.editingLaporan?.fotoPath;
    final showExistingFoto =
        _selectedFoto == null &&
        !_removeExistingFoto &&
        existingFoto != null &&
        existingFoto.isNotEmpty;

    return SingleChildScrollView(
      padding: EdgeInsets.fromLTRB(
        AppSpacing.lg,
        widget.isModal ? AppSpacing.sm : AppSpacing.lg,
        AppSpacing.lg,
        AppSpacing.lg + MediaQuery.of(context).viewInsets.bottom,
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Informasi Laporan',
              style: AppTextStyles.titleLarge,
            ),
            const SizedBox(height: AppSpacing.md),

            // Wilayah Dropdown
            Consumer<LaporanProvider>(
              builder: (context, laporanProvider, _) {
                if (laporanProvider.isLoadingWilayah) {
                  return const LoadingShimmer();
                }
                if (laporanProvider.referenceError != null &&
                    laporanProvider.wilayahList.isEmpty) {
                  return _buildReferenceErrorCard(
                    laporanProvider.referenceError!,
                    () => laporanProvider.loadWilayah(),
                  );
                }

                final hasSelected = laporanProvider.wilayahList.any(
                  (w) => w.id == _selectedWilayahId,
                );

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Wilayah', style: AppTextStyles.labelLarge),
                    const SizedBox(height: AppSpacing.sm),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.grey300),
                        borderRadius: BorderRadius.circular(AppBorderRadius.md),
                      ),
                      child: DropdownButton<int>(
                        isExpanded: true,
                        underline: const SizedBox(),
                        value: hasSelected ? _selectedWilayahId : null,
                        hint: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: AppSpacing.md),
                          child: Text('Pilih wilayah'),
                        ),
                        items: laporanProvider.wilayahList.map((wilayah) {
                          return DropdownMenuItem<int>(
                            value: wilayah.id,
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                              child: Text(
                                wilayah.displayName,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          );
                        }).toList(),
                        onChanged: (wilayahId) {
                          setState(() {
                            _selectedWilayahId = wilayahId;
                          });
                        },
                      ),
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: AppSpacing.lg),

            // Komoditas Dropdown
            Consumer<LaporanProvider>(
              builder: (context, laporanProvider, _) {
                if (laporanProvider.isLoadingKomoditas) {
                  return const LoadingShimmer();
                }
                if (laporanProvider.referenceError != null &&
                    laporanProvider.komoditasList.isEmpty) {
                  return _buildReferenceErrorCard(
                    laporanProvider.referenceError!,
                    () => laporanProvider.loadKomoditas(),
                  );
                }

                final hasSelected = laporanProvider.komoditasList.any(
                  (k) => k.id == _selectedKomoditasId,
                );

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Komoditas', style: AppTextStyles.labelLarge),
                    const SizedBox(height: AppSpacing.sm),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.grey300),
                        borderRadius: BorderRadius.circular(AppBorderRadius.md),
                      ),
                      child: DropdownButton<int>(
                        isExpanded: true,
                        underline: const SizedBox(),
                        value: hasSelected ? _selectedKomoditasId : null,
                        hint: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: AppSpacing.md),
                          child: Text('Pilih komoditas'),
                        ),
                        items: laporanProvider.komoditasList.map((komoditas) {
                          return DropdownMenuItem<int>(
                            value: komoditas.id,
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                              child: Text(
                                komoditas.displayName,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          );
                        }).toList(),
                        onChanged: (komoditasId) {
                          setState(() {
                            _selectedKomoditasId = komoditasId;
                          });
                        },
                      ),
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: AppSpacing.lg),

            // Harga Field
            CustomTextField(
              label: 'Harga per ${_satuanController.text.trim().isEmpty ? 'kg' : _satuanController.text.trim()}',
              hint: '0',
              controller: _hargaController,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              prefixIcon: const Icon(Icons.attach_money),
              validator: (value) {
                if (value?.trim().isEmpty ?? true) {
                  return 'Harga tidak boleh kosong';
                }
                final parsed = double.tryParse(value!.replaceAll(',', '.').trim());
                if (parsed == null) {
                  return 'Harga harus berupa angka';
                }
                if (parsed <= 0) {
                  return 'Harga harus lebih dari 0';
                }
                if (parsed > 999999999999.99) {
                  return 'Harga terlalu besar';
                }
                return null;
              },
            ),
            const SizedBox(height: AppSpacing.lg),

            // Satuan Field
            CustomTextField(
              label: 'Satuan',
              hint: 'kg',
              controller: _satuanController,
              prefixIcon: const Icon(Icons.scale),
              onChanged: (_) => setState(() {}),
              validator: (value) {
                if (value?.trim().isEmpty ?? true) {
                  return 'Satuan tidak boleh kosong';
                }
                return null;
              },
            ),
            const SizedBox(height: AppSpacing.lg),

            // Tanggal Panen Field
            InkWell(
              onTap: _selectDate,
              borderRadius: BorderRadius.circular(AppBorderRadius.md),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Tanggal Panen', style: AppTextStyles.labelLarge),
                  const SizedBox(height: AppSpacing.sm),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSpacing.md,
                      vertical: AppSpacing.md,
                    ),
                    decoration: BoxDecoration(
                      border: Border.all(color: AppColors.grey300),
                      borderRadius: BorderRadius.circular(AppBorderRadius.md),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          _selectedTanggalPanen != null
                              ? DateFormat('dd MMMM yyyy', 'id_ID')
                                  .format(_selectedTanggalPanen!)
                              : 'Pilih tanggal',
                          style: AppTextStyles.bodyMedium,
                        ),
                        const Icon(
                          Icons.calendar_today,
                          color: AppColors.primary,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Foto Bukti (opsional)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Foto Bukti (Opsional, maks. 2 MB)', style: AppTextStyles.labelLarge),
                const SizedBox(height: AppSpacing.sm),
                Wrap(
                  spacing: AppSpacing.md,
                  runSpacing: AppSpacing.sm,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    if (_selectedFoto != null)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(AppBorderRadius.md),
                        child: SizedBox(
                          width: 80,
                          height: 80,
                          child: Image.file(File(_selectedFoto!.path), fit: BoxFit.cover),
                        ),
                      )
                    else if (showExistingFoto)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(AppBorderRadius.md),
                        child: SizedBox(
                          width: 80,
                          height: 80,
                          child: CachedNetworkImage(
                            imageUrl: '${ApiConstants.storageUrl}/storage/$existingFoto',
                            fit: BoxFit.cover,
                            placeholder: (_, __) => const Center(child: CircularProgressIndicator()),
                            errorWidget: (_, __, ___) => const Icon(Icons.broken_image),
                          ),
                        ),
                      ),
                    TextButton.icon(
                      onPressed: _pickPhoto,
                      icon: const Icon(Icons.add_photo_alternate),
                      label: Text(
                        (_selectedFoto == null && !showExistingFoto) ? 'Pilih Foto' : 'Ganti Foto',
                      ),
                    ),
                    if (_selectedFoto != null)
                      IconButton(
                        tooltip: 'Hapus foto terpilih',
                        icon: const Icon(Icons.close, color: AppColors.error),
                        onPressed: () => setState(() => _selectedFoto = null),
                      )
                    else if (showExistingFoto)
                      TextButton.icon(
                        onPressed: () => setState(() => _removeExistingFoto = true),
                        icon: const Icon(Icons.delete_outline, color: AppColors.error),
                        label: const Text('Hapus Foto', style: TextStyle(color: AppColors.error)),
                      ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.lg),

            // Catatan Field
            CustomTextField(
              label: 'Catatan (Opsional)',
              hint: 'Tambahkan catatan untuk laporan ini',
              controller: _catatanController,
              maxLines: 4,
              minLines: 2,
              prefixIcon: const Icon(Icons.notes),
            ),
            const SizedBox(height: AppSpacing.xl),

            // Submit Button
            Consumer<LaporanProvider>(
              builder: (context, laporanProvider, _) {
                return PrimaryButton(
                  label: widget.editingLaporan != null ? 'Update Laporan' : 'Buat Laporan',
                  isLoading: laporanProvider.isLoading,
                  onPressed: _handleSubmit,
                );
              },
            ),

            const SizedBox(height: AppSpacing.xl),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final title = widget.editingLaporan != null ? 'Edit Laporan' : 'Buat Laporan Baru';

    if (widget.isModal) {
      return Column(
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: AppTextStyles.titleLarge),
                IconButton(
                  tooltip: 'Tutup',
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          Expanded(child: _buildFormContent(context)),
        ],
      );
    }

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.white),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Text(
          title,
          style: const TextStyle(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: _buildFormContent(context),
    );
  }
}

