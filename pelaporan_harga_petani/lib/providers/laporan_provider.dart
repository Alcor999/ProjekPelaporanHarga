import 'package:flutter/material.dart';
import '../core/exceptions/api_exception.dart';
import '../models/laporan_harga_model.dart';
import '../models/komoditas_model.dart';
import '../models/wilayah_model.dart';
import '../services/api_service.dart';

class LaporanProvider extends ChangeNotifier {
  final ApiService _apiService;

  // Private variables
  String? _errorMessage;
  bool _isLoadingKomoditas = false;
  bool _isLoadingWilayah = false;
  bool _isLoading = false;
  LaporanHarga? _selectedLaporan;
  List<Komoditas> _komoditasList = [];
  List<Wilayah> _wilayahList = [];
  List<LaporanHarga> _laporanList = [];

  // Constructor
  LaporanProvider(this._apiService);

  // Getters
  String? get errorMessage => _errorMessage;
  bool get isLoadingKomoditas => _isLoadingKomoditas;
  bool get isLoadingWilayah => _isLoadingWilayah;
  bool get isLoading => _isLoading;
  LaporanHarga? get selectedLaporan => _selectedLaporan;
  List<Komoditas> get komoditasList => _komoditasList;
  List<Wilayah> get wilayahList => _wilayahList;
  List<LaporanHarga> get laporanList => _laporanList;

  // Methods
  void reset() {
    _errorMessage = null;
    _selectedLaporan = null;
    _komoditasList = [];
    _wilayahList = [];
    _laporanList = [];
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  void setSelectedLaporan(LaporanHarga? laporan) {
    _selectedLaporan = laporan;
    notifyListeners();
  }

  Future<bool> deleteLaporan(int id) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      await _apiService.deleteLaporan(id);
      _laporanList.removeWhere((l) => l.id == id);
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updateLaporan(int id, LaporanHarga laporan) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      final updatedLaporan = await _apiService.updateLaporan(id, laporan);
      final index = _laporanList.indexWhere((l) => l.id == id);
      if (index != -1) {
        _laporanList[index] = updatedLaporan;
        _selectedLaporan = updatedLaporan;
      }
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } on ValidationException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createLaporan(LaporanHarga laporan) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      final response = await _apiService.createLaporan(laporan);
      final newLaporan = LaporanHarga.fromJson(response['data']);
      _laporanList.insert(0, newLaporan);
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } on ValidationException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadLaporanList({int page = 1}) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      final response = await _apiService.getLaporanList(page: page);
      final laporanData = response['data'];

      if (laporanData is List) {
        _laporanList = (laporanData as List)
            .map((l) => LaporanHarga.fromJson(l))
            .toList();
      } else if (laporanData is Map && laporanData.containsKey('data')) {
        _laporanList = (laporanData['data'] as List)
            .map((l) => LaporanHarga.fromJson(l))
            .toList();
      }
      notifyListeners();
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadKomoditas() async {
    try {
      _isLoadingKomoditas = true;
      _errorMessage = null;
      notifyListeners();

      _komoditasList = await _apiService.getKomoditas();
      notifyListeners();
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
    } finally {
      _isLoadingKomoditas = false;
      notifyListeners();
    }
  }

  Future<void> loadWilayah() async {
    try {
      _isLoadingWilayah = true;
      _errorMessage = null;
      notifyListeners();

      _wilayahList = await _apiService.getWilayah();
      notifyListeners();
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
    } finally {
      _isLoadingWilayah = false;
      notifyListeners();
    }
  }

  Future<void> initReferenceData() async {
    try {
      await Future.wait([loadKomoditas(), loadWilayah()]);
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
    }
  }
}
