import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../core/theme/app_theme.dart';
import '../models/laporan_harga_model.dart';
import '../models/wilayah_model.dart';
import '../models/komoditas_model.dart';
import '../providers/laporan_provider.dart';
import '../widgets/buttons.dart';
import '../widgets/custom_text_field.dart';
import '../widgets/common_widgets.dart';

class CreateLaporanScreen extends StatefulWidget {
  final LaporanHarga? editingLaporan;

  const CreateLaporanScreen({
    Key? key,
    this.editingLaporan,
  }) : super(key: key);

  @override
  State<CreateLaporanScreen> createState() => _CreateLaporanScreenState();
}

class _CreateLaporanScreenState extends State<CreateLaporanScreen> {
  late TextEditingController _hargaController;
  late TextEditingController _satuanController;
  late TextEditingController _catatanController;
  DateTime? _selectedTanggalPanen;
  Wilayah? _selectedWilayah;
  Komoditas? _selectedKomoditas;
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    _hargaController = TextEditingController(
      text: widget.editingLaporan?.harga.toString() ?? '',
    );
    _satuanController = TextEditingController(
      text: widget.editingLaporan?.satuan ?? 'kg',
    );
    _catatanController = TextEditingController(
      text: widget.editingLaporan?.catatan ?? '',
    );
    _selectedTanggalPanen = widget.editingLaporan?.tanggalPanen ?? DateTime.now();

    Future.microtask(() {
      final laporanProvider = context.read<LaporanProvider>();
      if (laporanProvider.wilayahList.isEmpty) {
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

  void _handleSubmit() async {
    if (_formKey.currentState?.validate() ?? false) {
      if (_selectedWilayah == null) {
        showErrorSnackBar(context, 'Pilih wilayah');
        return;
      }
      if (_selectedKomoditas == null) {
        showErrorSnackBar(context, 'Pilih komoditas');
        return;
      }

      final laporan = LaporanHarga(
        id: widget.editingLaporan?.id,
        userId: widget.editingLaporan?.userId ?? 0,
        wilayahId: _selectedWilayah!.id,
        komoditasId: _selectedKomoditas!.id,
        harga: double.parse(_hargaController.text),
        satuan: _satuanController.text.isEmpty ? 'kg' : _satuanController.text,
        tanggalPanen: _selectedTanggalPanen ?? DateTime.now(),
        catatan: _catatanController.text.isEmpty ? null : _catatanController.text,
        status: widget.editingLaporan?.status ?? 'pending',
      );

      final laporanProvider = context.read<LaporanProvider>();
      bool success;

      if (widget.editingLaporan != null) {
        success = await laporanProvider.updateLaporan(
          widget.editingLaporan!.id!,
          laporan,
        );
      } else {
        success = await laporanProvider.createLaporan(laporan);
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
        title: Text(
          widget.editingLaporan != null ? 'Edit Laporan' : 'Buat Laporan Baru',
          style: const TextStyle(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Informasi Laporan',
                style: AppTextStyles.titleLarge,
              ),
              SizedBox(height: AppSpacing.md),

              // Wilayah Dropdown
              Consumer<LaporanProvider>(
                builder: (context, laporanProvider, _) {
                  if (laporanProvider.isLoadingWilayah) {
                    return const LoadingShimmer();
                  }

                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Wilayah',
                        style: AppTextStyles.labelLarge,
                      ),
                      SizedBox(height: AppSpacing.sm),
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(color: AppColors.grey300),
                          borderRadius: BorderRadius.circular(AppBorderRadius.md),
                        ),
                        child: DropdownButton<Wilayah>(
                          isExpanded: true,
                          underline: const SizedBox(),
                          value: _selectedWilayah,
                          hint: const Padding(
                            padding: EdgeInsets.symmetric(horizontal: AppSpacing.md),
                            child: Text('Pilih wilayah'),
                          ),
                          items: laporanProvider.wilayahList.map((wilayah) {
                            return DropdownMenuItem<Wilayah>(
                              value: wilayah,
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                                child: Text(wilayah.displayName),
                              ),
                            );
                          }).toList(),
                          onChanged: (wilayah) {
                            setState(() {
                              _selectedWilayah = wilayah;
                            });
                          },
                        ),
                      ),
                    ],
                  );
                },
              ),
              SizedBox(height: AppSpacing.lg),

              // Komoditas Dropdown
              Consumer<LaporanProvider>(
                builder: (context, laporanProvider, _) {
                  if (laporanProvider.isLoadingKomoditas) {
                    return const LoadingShimmer();
                  }

                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Komoditas',
                        style: AppTextStyles.labelLarge,
                      ),
                      SizedBox(height: AppSpacing.sm),
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(color: AppColors.grey300),
                          borderRadius: BorderRadius.circular(AppBorderRadius.md),
                        ),
                        child: DropdownButton<Komoditas>(
                          isExpanded: true,
                          underline: const SizedBox(),
                          value: _selectedKomoditas,
                          hint: const Padding(
                            padding: EdgeInsets.symmetric(horizontal: AppSpacing.md),
                            child: Text('Pilih komoditas'),
                          ),
                          items: laporanProvider.komoditasList.map((komoditas) {
                            return DropdownMenuItem<Komoditas>(
                              value: komoditas,
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                                child: Text(komoditas.displayName),
                              ),
                            );
                          }).toList(),
                          onChanged: (komoditas) {
                            setState(() {
                              _selectedKomoditas = komoditas;
                            });
                          },
                        ),
                      ),
                    ],
                  );
                },
              ),
              SizedBox(height: AppSpacing.lg),

              // Harga Field
              CustomTextField(
                label: 'Harga per ${_satuanController.text.isEmpty ? 'kg' : _satuanController.text}',
                hint: '0',
                controller: _hargaController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                prefixIcon: const Icon(Icons.attach_money),
                validator: (value) {
                  if (value?.isEmpty ?? true) {
                    return 'Harga tidak boleh kosong';
                  }
                  if (double.tryParse(value!) == null) {
                    return 'Harga harus berupa angka';
                  }
                  return null;
                },
              ),
              SizedBox(height: AppSpacing.lg),

              // Satuan Field
              CustomTextField(
                label: 'Satuan',
                hint: 'kg',
                controller: _satuanController,
                prefixIcon: const Icon(Icons.scale),
                validator: (value) {
                  if (value?.isEmpty ?? true) {
                    return 'Satuan tidak boleh kosong';
                  }
                  return null;
                },
              ),
              SizedBox(height: AppSpacing.lg),

              // Tanggal Panen Field
              GestureDetector(
                onTap: _selectDate,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Tanggal Panen',
                      style: AppTextStyles.labelLarge,
                    ),
                    SizedBox(height: AppSpacing.sm),
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
              SizedBox(height: AppSpacing.lg),

              // Catatan Field
              CustomTextField(
                label: 'Catatan (Opsional)',
                hint: 'Tambahkan catatan untuk laporan ini',
                controller: _catatanController,
                maxLines: 4,
                minLines: 2,
                prefixIcon: const Icon(Icons.notes),
              ),
              SizedBox(height: AppSpacing.xl),

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

              SizedBox(height: AppSpacing.xl),
            ],
          ),
        ),
      ),
    );
  }
}

