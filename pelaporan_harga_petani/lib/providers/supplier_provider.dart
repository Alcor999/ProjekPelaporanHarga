import 'package:flutter/foundation.dart';
import '../services/api_service.dart';

class SupplierProvider extends ChangeNotifier {
  final ApiService _api;

  SupplierProvider(this._api);

  List<Map<String, dynamic>> _supplierList = [];
  Map<String, dynamic>? _supplierDetail;
  bool _loading = false;
  String? _error;

  List<Map<String, dynamic>> get supplierList => _supplierList;
  Map<String, dynamic>? get supplierDetail => _supplierDetail;
  bool get loading => _loading;
  String? get error => _error;

  Future<void> loadSuppliers({int? wilayahId, int? komoditasId}) async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      _supplierList = await _api.getSuppliers(wilayahId: wilayahId, komoditasId: komoditasId);
    } catch (e) {
      _error = e.toString();
    }
    _loading = false;
    notifyListeners();
  }

  Future<void> loadSupplierDetail(int id) async {
    _loading = true;
    _error = null;
    _supplierDetail = null;
    notifyListeners();
    try {
      _supplierDetail = await _api.getSupplierDetail(id);
    } catch (e) {
      _error = e.toString();
    }
    _loading = false;
    notifyListeners();
  }

  void clearDetail() {
    _supplierDetail = null;
    notifyListeners();
  }
}
