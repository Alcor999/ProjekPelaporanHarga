import 'package:flutter/material.dart';
import '../services/api_service.dart';

class HargaPublikProvider extends ChangeNotifier {
  final ApiService _apiService;

  List<dynamic> _hargaTerkini = [];
  List<dynamic> _hargaTren = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<dynamic> get hargaTerkini => _hargaTerkini;
  List<dynamic> get hargaTren => _hargaTren;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  HargaPublikProvider(this._apiService);

  Future<void> loadHargaTerkini({int? wilayahId, int? komoditasId}) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      final res = await _apiService.getHargaTerkini(
        wilayahId: wilayahId,
        komoditasId: komoditasId,
      );
      _hargaTerkini = res['data'] is List ? res['data'] : [];
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadHargaTren({int? wilayahId, int? komoditasId, int days = 30}) async {
    try {
      final res = await _apiService.getHargaTren(
        wilayahId: wilayahId,
        komoditasId: komoditasId,
        days: days,
      );
      _hargaTren = res['data'] is List ? res['data'] : [];
      notifyListeners();
    } catch (_) {
      _hargaTren = [];
      notifyListeners();
    }
  }
}
