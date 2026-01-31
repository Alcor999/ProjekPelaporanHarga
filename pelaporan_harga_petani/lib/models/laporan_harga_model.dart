import 'package:intl/intl.dart';

class LaporanHarga {
  final int? id;
  final int userId;
  final int wilayahId;
  final int komoditasId;
  final double harga;
  final String satuan;
  final DateTime tanggalPanen;
  final DateTime? tanggalLaporan;
  final String? catatan;
  final String status;
  final DateTime? createdAt;
  final String? rejectionReason;
  final String? fotoPath;

  // Relasi
  final Map<String, dynamic>? wilayah;
  final Map<String, dynamic>? komoditas;

  LaporanHarga({
    this.id,
    required this.userId,
    required this.wilayahId,
    required this.komoditasId,
    required this.harga,
    required this.satuan,
    required this.tanggalPanen,
    this.tanggalLaporan,
    this.catatan,
    required this.status,
    this.createdAt,
    this.rejectionReason,
    this.fotoPath,
    this.wilayah,
    this.komoditas,
  });

  factory LaporanHarga.fromJson(Map<String, dynamic> json) {
    return LaporanHarga(
      id: json['id'],
      userId: json['user_id'] ?? 0,
      wilayahId: json['wilayah_id'] ?? 0,
      komoditasId: json['komoditas_id'] ?? 0,
      harga: (json['harga'] is String) ? double.parse(json['harga']) : (json['harga'] ?? 0.0).toDouble(),
      satuan: json['satuan'] ?? 'kg',
      tanggalPanen: json['tanggal_panen'] != null ? DateTime.parse(json['tanggal_panen']) : DateTime.now(),
      tanggalLaporan: json['tanggal_laporan'] != null ? DateTime.parse(json['tanggal_laporan']) : null,
      catatan: json['catatan'],
      status: json['status'] ?? 'pending',
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : null,
      rejectionReason: json['rejection_reason'],
      fotoPath: json['foto_path'],
      wilayah: json['wilayah'],
      komoditas: json['komoditas'],
    );
  }

  Map<String, dynamic> toJson({String? fotoBase64}) {
    final m = {
      'user_id': userId,
      'wilayah_id': wilayahId,
      'komoditas_id': komoditasId,
      'harga': harga,
      'satuan': satuan,
      'tanggal_panen': DateFormat('yyyy-MM-dd').format(tanggalPanen),
      'catatan': catatan,
    };
    if (fotoBase64 != null && fotoBase64.isNotEmpty) m['foto'] = fotoBase64;
    return m;
  }

  String get formattedHarga => 'Rp${harga.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}/$satuan';

  String get formattedTanggalPanen => DateFormat('dd MMMM yyyy', 'id_ID').format(tanggalPanen);

  String get statusLabel {
    switch (status) {
      case 'approved':
        return 'Disetujui';
      case 'rejected':
        return 'Ditolak';
      case 'pending':
        return 'Menunggu';
      default:
        return status;
    }
  }

  LaporanHarga copyWith({
    int? id,
    int? userId,
    int? wilayahId,
    int? komoditasId,
    double? harga,
    String? satuan,
    DateTime? tanggalPanen,
    DateTime? tanggalLaporan,
    String? catatan,
    String? status,
    DateTime? createdAt,
    Map<String, dynamic>? wilayah,
    Map<String, dynamic>? komoditas,
  }) {
    return LaporanHarga(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      wilayahId: wilayahId ?? this.wilayahId,
      komoditasId: komoditasId ?? this.komoditasId,
      harga: harga ?? this.harga,
      satuan: satuan ?? this.satuan,
      tanggalPanen: tanggalPanen ?? this.tanggalPanen,
      tanggalLaporan: tanggalLaporan ?? this.tanggalLaporan,
      catatan: catatan ?? this.catatan,
      status: status ?? this.status,
      createdAt: createdAt ?? this.createdAt,
      wilayah: wilayah ?? this.wilayah,
      komoditas: komoditas ?? this.komoditas,
    );
  }
}

