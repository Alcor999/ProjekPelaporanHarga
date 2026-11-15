import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../core/constants/api_constants.dart';
import '../core/exceptions/api_exception.dart';
import '../models/user_model.dart';
import '../models/wilayah_model.dart';
import '../models/komoditas_model.dart';
import '../models/laporan_harga_model.dart';

class ApiService {
  late Dio _dio;
  String? _token;
  VoidCallback? onUnauthorized;

  ApiService({Dio? dio}) {
    if (dio != null) {
      _dio = dio;
    } else {
      _initializeDio();
    }
    _attachInterceptors();
  }

  void _initializeDio() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: ApiConstants.connectionTimeout,
        receiveTimeout: ApiConstants.receiveTimeout,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );
  }

  void _attachInterceptors() {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          if (_token != null) {
            options.headers['Authorization'] = 'Bearer $_token';
          }
          return handler.next(options);
        },
        onError: (error, handler) {
          final statusCode = error.response?.statusCode;
          final path = error.requestOptions.path;
          final isAuthEndpoint =
              path.endsWith(ApiConstants.login) ||
              path.endsWith(ApiConstants.register) ||
              path.endsWith(ApiConstants.logout);
          if (statusCode == 401 && !isAuthEndpoint) {
            onUnauthorized?.call();
          }
          return handler.next(error);
        },
      ),
    );
  }

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('api_token');
  }

  Future<User?> getCachedUser() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString('user');
      if (raw == null || raw.isEmpty) return null;
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) {
        return User.fromJson(decoded);
      }
      if (decoded is Map) {
        return User.fromJson(Map<String, dynamic>.from(decoded));
      }
    } catch (_) {
      // Abaikan cache korup dan tunggu pembaruan profil dari server
    }
    return null;
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post(
        ApiConstants.login,
        data: {'email': email, 'password': password},
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          _token = data['data']['token'];

          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('api_token', _token!);
          await prefs.setString('user', jsonEncode(data['data']['user']));

          return data;
        }
      }
      throw ServerException(message: 'Login gagal');
    } on DioException catch (e) {
      // Handle DioException dan throw ApiException yang sesuai
      _handleDioException(e);
      rethrow;
    } catch (e) {
      // Handle error lainnya
      if (e is ApiException) {
        rethrow;
      }
      throw ApiException(
        message: 'Terjadi kesalahan: ${e.toString()}',
        originalError: e,
      );
    }
  }

  Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password,
    String passwordConfirmation, {
    String? phone,
    String? address,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.register,
        data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'phone': phone,
          'address': address,
        },
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return data;
        }
      }
      throw ServerException(message: 'Registrasi gagal');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<User> getProfile() async {
    try {
      final response = await _dio.get(ApiConstants.profile);

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          final user = User.fromJson(Map<String, dynamic>.from(data['data'] as Map));
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('user', jsonEncode(user.toJson()));
          return user;
        }
      }
      throw ServerException(message: 'Gagal mengambil profil');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    } finally {
      await clearSessionData();
    }
  }

  Future<List<Wilayah>> getWilayah() async {
    try {
      final response = await _dio.get(ApiConstants.wilayah);

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          final List<dynamic> wilayahList = data['data'] ?? [];
          return wilayahList.map((w) => Wilayah.fromJson(w)).toList();
        }
      }
      return [];
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<List<Komoditas>> getKomoditas() async {
    try {
      final response = await _dio.get(ApiConstants.komoditas);

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          final List<dynamic> komoditasList = data['data'] ?? [];
          return komoditasList.map((k) => Komoditas.fromJson(k)).toList();
        }
      }
      return [];
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> createLaporan(LaporanHarga laporan, {String? fotoBase64}) async {
    try {
      final data = laporan.toJson(fotoBase64: fotoBase64);
      final response = await _dio.post(
        ApiConstants.laporan,
        data: data,
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return data;
        }
      }
      throw ServerException(message: 'Gagal membuat laporan');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getLaporanList({
    int page = 1,
    String? status,
    int? wilayahId,
    int? komoditasId,
    String? search,
    String? tanggalDari,
    String? tanggalSampai,
  }) async {
    try {
      final params = <String, dynamic>{'page': page};
      if (status != null) params['status'] = status;
      if (wilayahId != null) params['wilayah_id'] = wilayahId;
      if (komoditasId != null) params['komoditas_id'] = komoditasId;
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (tanggalDari != null) params['tanggal_dari'] = tanggalDari;
      if (tanggalSampai != null) params['tanggal_sampai'] = tanggalSampai;
      final response = await _dio.get(
        ApiConstants.laporan,
        queryParameters: params,
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return data;
        }
      }
      throw ServerException(message: 'Gagal mengambil daftar laporan');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<LaporanHarga> getLaporanDetail(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.laporanDetail}/$id');

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return LaporanHarga.fromJson(data['data']);
        }
      }
      throw ServerException(message: 'Gagal mengambil detail laporan');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<LaporanHarga> updateLaporan(
    int id,
    LaporanHarga laporan, {
    String? fotoBase64,
    bool removeFoto = false,
  }) async {
    try {
      final data = laporan.toJson(
        fotoBase64: fotoBase64,
        removeFoto: removeFoto,
      );
      final response = await _dio.put(
        '${ApiConstants.laporanDetail}/$id',
        data: data,
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return LaporanHarga.fromJson(data['data']);
        }
      }
      throw ServerException(message: 'Gagal mengupdate laporan');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<void> deleteLaporan(int id) async {
    try {
      final response = await _dio.delete('${ApiConstants.laporanDetail}/$id');

      if (response.statusCode != 200 && response.statusCode != 204) {
        throw ServerException(message: 'Gagal menghapus laporan');
      }
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getHargaTerkini({
    int? wilayahId,
    int? komoditasId,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (wilayahId != null) params['wilayah_id'] = wilayahId;
      if (komoditasId != null) params['komoditas_id'] = komoditasId;
      final response = await _dio.get(
        ApiConstants.hargaTerkini,
        queryParameters: params.isNotEmpty ? params : null,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data;
      }
      throw ServerException(message: 'Gagal mengambil data harga');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getHargaTren({
    int? wilayahId,
    int? komoditasId,
    int days = 30,
  }) async {
    try {
      final params = <String, dynamic>{'days': days};
      if (wilayahId != null) params['wilayah_id'] = wilayahId;
      if (komoditasId != null) params['komoditas_id'] = komoditasId;
      final response = await _dio.get(
        ApiConstants.hargaTren,
        queryParameters: params,
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data;
      }
      throw ServerException(message: 'Gagal mengambil data tren');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getNotifications({int page = 1, bool unreadOnly = false}) async {
    try {
      final response = await _dio.get(
        ApiConstants.notifications,
        queryParameters: {'page': page, 'unread_only': unreadOnly},
      );
      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data;
      }
      throw ServerException(message: 'Gagal mengambil notifikasi');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<int> getNotificationsUnreadCount() async {
    try {
      final response = await _dio.get(ApiConstants.notificationsUnreadCount);
      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data['data']['count'] ?? 0;
      }
      return 0;
    } catch (_) {
      return 0;
    }
  }

  Future<void> markNotificationAsRead(int id) async {
    try {
      await _dio.post(ApiConstants.notificationRead(id));
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<void> markAllNotificationsAsRead() async {
    try {
      await _dio.post(ApiConstants.notificationsReadAll);
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  // Suppliers (public - no auth required for list/detail)
  Future<List<Map<String, dynamic>>> getSuppliers({int? wilayahId, int? komoditasId}) async {
    try {
      final params = <String, dynamic>{};
      if (wilayahId != null) params['wilayah_id'] = wilayahId;
      if (komoditasId != null) params['komoditas_id'] = komoditasId;
      final response = await _dio.get(ApiConstants.suppliers, queryParameters: params.isEmpty ? null : params);
      if (response.statusCode == 200) {
        final data = response.data;
        final list = data is Map && data['data'] != null ? data['data'] : (data is List ? data : []);
        return List<Map<String, dynamic>>.from((list as List).map((e) => Map<String, dynamic>.from(e as Map)));
      }
      return [];
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>?> getSupplierDetail(int id) async {
    try {
      final response = await _dio.get(ApiConstants.supplierDetail(id));
      if (response.statusCode == 200) {
        final data = response.data;
        if (data is Map) return Map<String, dynamic>.from(data);
      }
      return null;
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  // Penyetoran (petani - auth required)
  Future<Map<String, dynamic>> getPenyetoranList({int page = 1, String? status}) async {
    try {
      final params = <String, dynamic>{'page': page};
      if (status != null && status.isNotEmpty) params['status'] = status;
      final response = await _dio.get(ApiConstants.penyetoran, queryParameters: params);
      if (response.statusCode == 200) {
        return Map<String, dynamic>.from(response.data as Map);
      }
      throw ServerException(message: 'Gagal mengambil daftar penyetoran');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> createPenyetoran({
    required int supplierId,
    required int komoditasId,
    required double jumlah,
    required String tanggal,
    String? satuan,
    String? catatan,
  }) async {
    try {
      final data = {
        'supplier_id': supplierId,
        'komoditas_id': komoditasId,
        'jumlah': jumlah,
        'tanggal': tanggal,
        if (satuan != null) 'satuan': satuan,
        if (catatan != null) 'catatan': catatan,
      };
      final response = await _dio.post(ApiConstants.penyetoran, data: data);
      if (response.statusCode == 201 || response.statusCode == 200) {
        return Map<String, dynamic>.from(response.data as Map);
      }
      throw ServerException(message: 'Gagal mencatat penyetoran');
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  Future<void> submitSupplierRating(int supplierId, {required int rating, String? ulasan, int? penyetoranId}) async {
    try {
      final data = {'rating': rating, if (ulasan != null) 'ulasan': ulasan, if (penyetoranId != null) 'penyetoran_id': penyetoranId};
      final response = await _dio.post(ApiConstants.supplierRating(supplierId), data: data);
      if (response.statusCode != 201 && response.statusCode != 200) {
        throw ServerException(message: 'Gagal menyimpan rating');
      }
    } on DioException catch (e) {
      _handleDioException(e);
      rethrow;
    }
  }

  void _handleDioException(DioException e) {
    if (e.type == DioExceptionType.connectionTimeout) {
      throw NetworkException(
        message: 'Koneksi timeout. Pastikan server tersedia dan internet stabil.',
      );
    }

    if (e.type == DioExceptionType.sendTimeout ||
        e.type == DioExceptionType.receiveTimeout) {
      throw NetworkException(
        message: 'Server tidak merespons. Cek koneksi internet Anda.',
      );
    }

    if (e.type == DioExceptionType.connectionError ||
        e.type == DioExceptionType.unknown) {
      final errorMessage = e.error?.toString() ?? '';
      if (errorMessage.contains('Connection refused') ||
          errorMessage.contains('Failed to connect') ||
          errorMessage.contains('SocketException')) {
        throw NetworkException(
          message: 'Gagal terhubung ke server. Cek koneksi internet Anda.',
        );
      }
      throw NetworkException(
        message: 'Koneksi internet terputus atau server tidak tersedia.',
      );
    }

    if (e.response != null) {
      final statusCode = e.response!.statusCode;
      final data = e.response!.data;

      switch (statusCode) {
        case 401:
          throw UnauthorizedException(
            message: data?['message'] ?? 'Tidak terotorisasi',
          );
        case 403:
          throw ForbiddenException(
            message: data?['message'] ?? 'Akses ditolak',
          );
        case 404:
          throw NotFoundException(
            message: data?['message'] ?? 'Data tidak ditemukan',
          );
        case 422:
          final errors = data?['errors'] ?? {};
          throw ValidationException(
            message: data?['message'] ?? 'Validasi gagal',
            errors: Map<String, List<String>>.from(
              errors.map((key, value) {
                final errorList = (value is List)
                    ? List<String>.from(value)
                    : [value.toString()];
                return MapEntry(key, errorList);
              }),
            ),
          );
        case 500:
          throw ServerException(message: data?['message'] ?? 'Server error');
        default:
          throw ApiException(
            message:
                data?['message'] ?? 'Error: ${statusCode ?? 'Tidak diketahui'}',
            statusCode: statusCode,
          );
      }
    }

    // Generic error with more details
    throw ApiException(
      message: 'Terjadi kesalahan: ${e.message ?? 'Error tidak diketahui'}',
      originalError: e,
    );
  }

  bool isAuthenticated() => _token != null;

  String? getToken() => _token;

  Future<void> clearSessionData() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('api_token');
    await prefs.remove('user');
  }

  Future<void> clearToken() => clearSessionData();
}
