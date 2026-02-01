import 'package:flutter/foundation.dart';
import '../services/api_service.dart';

class PenyetoranProvider extends ChangeNotifier {
  final ApiService _api;

  PenyetoranProvider(this._api);

  List<Map<String, dynamic>> _penyetoranList = [];
  int? _total;
  bool _loading = false;
  String? _error;

  List<Map<String, dynamic>> get penyetoranList => _penyetoranList;
  int? get total => _total;
  bool get loading => _loading;
  String? get error => _error;

  Future<void> loadPenyetoranList({int page = 1, String? status}) async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      final res = await _api.getPenyetoranList(page: page, status: status);
      final data = res['data'] as List?;
      _penyetoranList = data != null
          ? data.map((e) => Map<String, dynamic>.from(e as Map)).toList()
          : [];
      _total = res['total'] as int?;
    } catch (e) {
      _error = e.toString();
    }
    _loading = false;
    notifyListeners();
  }

  Future<bool> createPenyetoran({
    required int supplierId,
    required int komoditasId,
    required double jumlah,
    required String tanggal,
    String? satuan,
    String? catatan,
  }) async {
    try {
      await _api.createPenyetoran(
        supplierId: supplierId,
        komoditasId: komoditasId,
        jumlah: jumlah,
        tanggal: tanggal,
        satuan: satuan,
        catatan: catatan,
      );
      return true;
    } catch (e) {
      return false;
    }
  }

  Future<bool> submitRating(int supplierId, {required int rating, String? ulasan, int? penyetoranId}) async {
    try {
      await _api.submitSupplierRating(supplierId, rating: rating, ulasan: ulasan, penyetoranId: penyetoranId);
      return true;
    } catch (e) {
      return false;
    }
  }
}
