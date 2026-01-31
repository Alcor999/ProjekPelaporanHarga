class Wilayah {
  final int id;
  final String desa;
  final String kecamatan;
  final String kabupaten;
  final String provinsi;
  final bool isActive;

  Wilayah({
    required this.id,
    required this.desa,
    required this.kecamatan,
    required this.kabupaten,
    required this.provinsi,
    required this.isActive,
  });

  factory Wilayah.fromJson(Map<String, dynamic> json) {
    return Wilayah(
      id: json['id'] ?? 0,
      desa: json['desa'] ?? '',
      kecamatan: json['kecamatan'] ?? '',
      kabupaten: json['kabupaten'] ?? '',
      provinsi: json['provinsi'] ?? '',
      isActive: json['is_active'] ?? true,
    );
  }

  String get fullAddress => '$desa, $kecamatan, $kabupaten, $provinsi';
  String get displayName => '$desa - $kecamatan';

  Map<String, dynamic> toJson() => {
    'id': id,
    'desa': desa,
    'kecamatan': kecamatan,
    'kabupaten': kabupaten,
    'provinsi': provinsi,
    'is_active': isActive,
  };
}

