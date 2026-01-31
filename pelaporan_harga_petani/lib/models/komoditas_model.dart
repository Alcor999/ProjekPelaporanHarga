class Komoditas {
  final int id;
  final String name;
  final String? grade;
  final String? description;
  final bool isActive;
  final String? kategori;

  Komoditas({
    required this.id,
    required this.name,
    this.grade,
    this.description,
    required this.isActive,
    this.kategori,
  });

  factory Komoditas.fromJson(Map<String, dynamic> json) {
    return Komoditas(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      grade: json['grade'],
      description: json['description'],
      isActive: json['is_active'] ?? true,
      kategori: json['kategori'],
    );
  }

  String get displayName => grade != null && grade!.isNotEmpty ? '$name ($grade)' : name;

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'grade': grade ?? '',
    'description': description,
    'is_active': isActive,
  };
}

