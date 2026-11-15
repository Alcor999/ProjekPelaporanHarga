import 'package:flutter/foundation.dart';
import '../core/exceptions/api_exception.dart';
import '../services/api_service.dart';

class PenyetoranProvider extends ChangeNotifier {
  final ApiService _api;

  PenyetoranProvider(this._api);

  List<Map<String, dynamic>> _penyetoranList = [];
  int? _total;
  int? _lastCreatedPenyetoranId;
  bool _loading = false;
  String? _error;

  List<Map<String, dynamic>> get penyetoranList => _penyetoranList;
  int? get total => _total;
  int? get lastCreatedPenyetoranId => _lastCreatedPenyetoranId;
  bool get loading => _loading;
  String? get error => _error;

  void clearError() {
    _error = null;
    notifyListeners();
  }

  void reset() {
    _penyetoranList = [];
    _total = null;
    _lastCreatedPenyetoranId = null;
    _loading = false;
    _error = null;
    notifyListeners();
  }

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
    } on ApiException catch (e) {
      _error = e.message;
    } catch (e) {
      _error = e.toString();
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<bool> createPenyetoran({
    required int supplierId,
    required int komoditasId,
    required double jumlah,
    required String tanggal,
    String? satuan,
    String? catatan,
  }) async {
    _error = null;
    _lastCreatedPenyetoranId = null;
    try {
      final res = await _api.createPenyetoran(
        supplierId: supplierId,
        komoditasId: komoditasId,
        jumlah: jumlah,
        tanggal: tanggal,
        satuan: satuan,
        catatan: catatan,
      );
      final data = res['data'];
      if (data is Map && data['id'] is num) {
        _lastCreatedPenyetoranId = (data['id'] as num).toInt();
      }
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> submitRating(int supplierId, {required int rating, String? ulasan, int? penyetoranId}) async {
    _error = null;
    try {
      await _api.submitSupplierRating(supplierId, rating: rating, ulasan: ulasan, penyetoranId: penyetoranId);
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}
