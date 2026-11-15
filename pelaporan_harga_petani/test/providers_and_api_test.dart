import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:pelaporan_harga_petani/core/constants/api_constants.dart';
import 'package:pelaporan_harga_petani/core/exceptions/api_exception.dart';
import 'package:pelaporan_harga_petani/models/komoditas_model.dart';
import 'package:pelaporan_harga_petani/models/laporan_harga_model.dart';
import 'package:pelaporan_harga_petani/models/user_model.dart';
import 'package:pelaporan_harga_petani/models/wilayah_model.dart';
import 'package:pelaporan_harga_petani/providers/auth_provider.dart';
import 'package:pelaporan_harga_petani/providers/laporan_provider.dart';
import 'package:pelaporan_harga_petani/providers/notification_provider.dart';
import 'package:pelaporan_harga_petani/providers/penyetoran_provider.dart';
import 'package:pelaporan_harga_petani/services/api_service.dart';

class _StubAdapter implements HttpClientAdapter {
  final Future<ResponseBody> Function(RequestOptions options) handler;

  _StubAdapter(this.handler);

  @override
  void close({bool force = false}) {}

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) =>
      handler(options);
}

class _FakeApiService extends ApiService {
  bool profileThrowsNetwork = false;
  bool profileThrowsUnauthorized = false;
  bool updateThrowsValidation = false;
  bool referenceThrowsNetwork = false;
  bool penyetoranCreateThrows = false;
  bool notificationMarkThrows = false;
  int logoutCalls = 0;

  @override
  Future<User> getProfile() async {
    if (profileThrowsUnauthorized) {
      throw UnauthorizedException(message: 'Token tidak valid');
    }
    if (profileThrowsNetwork) {
      throw NetworkException(message: 'Tidak ada koneksi internet');
    }
    return User(
      id: 1,
      name: 'Petani Segar',
      email: 'petani@example.com',
      role: 'petani',
      status: 'active',
    );
  }

  @override
  Future<void> logout() async {
    logoutCalls++;
    await clearSessionData();
  }

  @override
  Future<LaporanHarga> updateLaporan(
    int id,
    LaporanHarga laporan, {
    String? fotoBase64,
    bool removeFoto = false,
  }) async {
    if (updateThrowsValidation) {
      throw ValidationException(
        message: 'Validasi gagal',
        errors: {
          'harga': ['Harga wajib lebih besar dari 0'],
        },
      );
    }
    return laporan.copyWith(id: id);
  }

  @override
  Future<List<Komoditas>> getKomoditas() async {
    if (referenceThrowsNetwork) {
      throw NetworkException(message: 'Gagal memuat komoditas');
    }
    return [Komoditas(id: 1, name: 'Gabah', isActive: true)];
  }

  @override
  Future<List<Wilayah>> getWilayah() async => [];

  @override
  Future<Map<String, dynamic>> createPenyetoran({
    required int supplierId,
    required int komoditasId,
    required double jumlah,
    required String tanggal,
    String? satuan,
    String? catatan,
  }) async {
    if (penyetoranCreateThrows) {
      throw ValidationException(message: 'Jumlah harus positif', errors: {});
    }
    return {'success': true};
  }

  @override
  Future<void> markNotificationAsRead(int id) async {
    if (notificationMarkThrows) {
      throw NetworkException(message: 'Gagal memperbarui notifikasi');
    }
  }
}

ResponseBody _jsonResponse(Object payload, {int status = 200}) {
  return ResponseBody.fromString(
    jsonEncode(payload),
    status,
    headers: {
      Headers.contentTypeHeader: [Headers.jsonContentType],
    },
  );
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('ApiConstants uses API_BASE_URL compile-time default safely', () {
    expect(ApiConstants.baseUrl, 'http://10.0.2.2:8000/api');
    expect(ApiConstants.storageUrl, 'http://10.0.2.2:8000');
  });

  test('ApiService login encodes cached user as valid JSON and triggers onUnauthorized on 401', () async {
    SharedPreferences.setMockInitialValues({});
    var unauthorizedTriggered = false;

    final dio = Dio(BaseOptions(baseUrl: ApiConstants.baseUrl));
    dio.httpClientAdapter = _StubAdapter((options) async {
      if (options.path == ApiConstants.login) {
        return _jsonResponse({
          'success': true,
          'data': {
            'token': 'tok-123',
            'user': {
              'id': 7,
              'name': 'Budi Tani',
              'email': 'budi@example.com',
              'role': 'petani',
              'status': 'active',
            },
          },
        });
      }
      return _jsonResponse({'message': 'Unauthenticated'}, status: 401);
    });

    final api = ApiService(dio: dio)
      ..onUnauthorized = () {
        unauthorizedTriggered = true;
      };

    await api.login('budi@example.com', 'rahasia');
    final cached = await api.getCachedUser();
    expect(cached, isNotNull);
    expect(cached!.email, 'budi@example.com');

    await expectLater(api.getProfile(), throwsA(isA<UnauthorizedException>()));
    expect(unauthorizedTriggered, isTrue);
  });

  test('AuthProvider.init preserves offline session from JSON cache when network fails', () async {
    SharedPreferences.setMockInitialValues({
      'api_token': 'tok-offline',
      'user': jsonEncode({
        'id': 2,
        'name': 'Siti Petani',
        'email': 'siti@example.com',
        'role': 'petani',
        'status': 'active',
      }),
    });

    final api = _FakeApiService()..profileThrowsNetwork = true;
    final auth = AuthProvider(api);
    await auth.init();

    expect(auth.isInitializing, isFalse);
    expect(auth.isAuthenticated, isTrue);
    expect(auth.isOfflineSession, isTrue);
    expect(auth.initError, isNotNull);
    expect(auth.user?.name, 'Siti Petani');
    expect(api.logoutCalls, 0);
  });

  test('AuthProvider.init clears local token cleanly on 401 without calling logout endpoint', () async {
    SharedPreferences.setMockInitialValues({
      'api_token': 'tok-expired',
      'user': jsonEncode({
        'id': 2,
        'name': 'Siti Petani',
        'email': 'siti@example.com',
        'role': 'petani',
        'status': 'active',
      }),
    });

    final api = _FakeApiService()..profileThrowsUnauthorized = true;
    final auth = AuthProvider(api);
    await auth.init();

    expect(auth.isInitializing, isFalse);
    expect(auth.isAuthenticated, isFalse);
    expect(api.logoutCalls, 0);
    expect(api.isAuthenticated(), isFalse);
  });

  test('LaporanProvider menangkap ValidationException sebelum ApiException dan memisahkan referenceError', () async {
    SharedPreferences.setMockInitialValues({});
    final api = _FakeApiService()
      ..updateThrowsValidation = true
      ..referenceThrowsNetwork = true;
    final provider = LaporanProvider(api);

    final ok = await provider.updateLaporan(
      1,
      LaporanHarga(
        userId: 1,
        wilayahId: 1,
        komoditasId: 1,
        harga: 5000,
        satuan: 'kg',
        tanggalPanen: DateTime(2026, 11, 15),
        status: 'pending',
      ),
    );
    expect(ok, isFalse);
    expect(provider.errorMessage, 'Harga wajib lebih besar dari 0');

    await provider.initReferenceData();
    expect(provider.referenceError, 'Gagal memuat komoditas');
    expect(provider.errorMessage, 'Harga wajib lebih besar dari 0');
  });

  test('PenyetoranProvider dan NotificationProvider menyimpan error alih-alih menelannya', () async {
    SharedPreferences.setMockInitialValues({});
    final api = _FakeApiService()
      ..penyetoranCreateThrows = true
      ..notificationMarkThrows = true;

    final penyetoranProvider = PenyetoranProvider(api);
    final created = await penyetoranProvider.createPenyetoran(
      supplierId: 1,
      komoditasId: 1,
      jumlah: 10,
      tanggal: '2026-11-15',
    );
    expect(created, isFalse);
    expect(penyetoranProvider.error, 'Jumlah harus positif');

    final notificationProvider = NotificationProvider(api);
    final marked = await notificationProvider.markAsRead(5);
    expect(marked, isFalse);
    expect(notificationProvider.errorMessage, 'Gagal memperbarui notifikasi');
  });
}
