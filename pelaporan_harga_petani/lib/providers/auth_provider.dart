import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/user_model.dart';
import '../core/exceptions/api_exception.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService;

  User? _user;
  String? _token;
  bool _isLoading = false;
  bool _isInitializing = true;
  bool _isOfflineSession = false;
  String? _errorMessage;
  String? _initError;
  bool _isAuthenticated = false;
  VoidCallback? onSessionExpired;

  AuthProvider(this._apiService) {
    _apiService.onUnauthorized = () {
      if (_isAuthenticated || _token != null) {
        clearSession();
      }
    };
  }

  // Getters
  User? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  bool get isInitializing => _isInitializing;
  bool get isOfflineSession => _isOfflineSession;
  String? get errorMessage => _errorMessage;
  String? get initError => _initError;
  bool get isAuthenticated => _isAuthenticated;

  Future<void> init() async {
    _isInitializing = true;
    _initError = null;
    notifyListeners();

    try {
      await _apiService.loadToken();
      if (_apiService.isAuthenticated()) {
        _token = _apiService.getToken();
        _user = await _apiService.getCachedUser();
        try {
          _user = await _apiService.getProfile();
          _isAuthenticated = true;
          _isOfflineSession = false;
          _initError = null;
        } on UnauthorizedException catch (e) {
          await clearSession(errorMessage: e.message);
        } on ForbiddenException catch (e) {
          await clearSession(errorMessage: e.message);
        } on NetworkException catch (e) {
          if (_user != null) {
            _isAuthenticated = true;
            _isOfflineSession = true;
            _initError = e.message;
          } else {
            _isAuthenticated = false;
            _isOfflineSession = false;
            _initError = e.message;
          }
        } on ApiException catch (e) {
          if (_user != null) {
            _isAuthenticated = true;
            _isOfflineSession = true;
            _initError = e.message;
          } else {
            _isAuthenticated = false;
            _isOfflineSession = false;
            _initError = e.message;
          }
        }
      } else {
        _user = null;
        _token = null;
        _isAuthenticated = false;
        _isOfflineSession = false;
      }
    } finally {
      _isInitializing = false;
      notifyListeners();
    }
  }

  Future<bool> refreshProfile() async {
    try {
      _user = await _apiService.getProfile();
      _isAuthenticated = true;
      _isOfflineSession = false;
      _initError = null;
      notifyListeners();
      return true;
    } on UnauthorizedException catch (e) {
      await clearSession(errorMessage: e.message);
      return false;
    } on ForbiddenException catch (e) {
      await clearSession(errorMessage: e.message);
      return false;
    } on ApiException catch (e) {
      _initError = e.message;
      notifyListeners();
      return false;
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.login(email, password);
      _user = User.fromJson(Map<String, dynamic>.from(response['data']['user'] as Map));
      _token = response['data']['token'];
      _isAuthenticated = true;
      _isOfflineSession = false;
      _initError = null;
      _errorMessage = null;
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

  Future<bool> register(
    String name,
    String email,
    String password,
    String passwordConfirmation, {
    String? phone,
    String? address,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _apiService.register(
        name,
        email,
        password,
        passwordConfirmation,
        phone: phone,
        address: address,
      );
      _errorMessage = null;
      notifyListeners();
      return true;
    } on ValidationException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> clearSession({String? errorMessage}) async {
    final wasAuthenticated = _isAuthenticated;
    await _apiService.clearSessionData();
    _user = null;
    _token = null;
    _isAuthenticated = false;
    _isOfflineSession = false;
    _errorMessage = errorMessage;
    notifyListeners();
    if (wasAuthenticated) {
      onSessionExpired?.call();
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (_) {
      await _apiService.clearSessionData();
    }

    _user = null;
    _token = null;
    _isAuthenticated = false;
    _isOfflineSession = false;
    _initError = null;
    _errorMessage = null;
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}

