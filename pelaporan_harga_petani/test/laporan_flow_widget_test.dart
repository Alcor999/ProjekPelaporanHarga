import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:pelaporan_harga_petani/core/exceptions/api_exception.dart';
import 'package:pelaporan_harga_petani/models/komoditas_model.dart';
import 'package:pelaporan_harga_petani/models/laporan_harga_model.dart';
import 'package:pelaporan_harga_petani/models/user_model.dart';
import 'package:pelaporan_harga_petani/models/wilayah_model.dart';
import 'package:pelaporan_harga_petani/providers/auth_provider.dart';
import 'package:pelaporan_harga_petani/providers/laporan_provider.dart';
import 'package:pelaporan_harga_petani/providers/notification_provider.dart';
import 'package:pelaporan_harga_petani/providers/penyetoran_provider.dart';
import 'package:pelaporan_harga_petani/screens/create_laporan_screen.dart';
import 'package:pelaporan_harga_petani/screens/dashboard_screen.dart';
import 'package:pelaporan_harga_petani/screens/laporan_detail_screen.dart';
import 'package:pelaporan_harga_petani/services/api_service.dart';

class _MockLaporanApiService extends ApiService {
  bool failProfileWithNetwork = false;
  List<LaporanHarga> seededLaporan = [];

  @override
  Future<User> getProfile() async {
    if (failProfileWithNetwork) {
      throw NetworkException(message: 'network offline');
    }
    return User(
      id: 1,
      name: 'Petani Uji',
      email: 'petani@example.com',
      role: 'petani',
      status: 'active',
    );
  }

  @override
  Future<List<Wilayah>> getWilayah() async => [
        Wilayah(
          id: 10,
          desa: 'Sukamaju',
          kecamatan: 'Cianjur',
          kabupaten: 'Kab. Cianjur',
          provinsi: 'Jawa Barat',
          isActive: true,
        ),
      ];

  @override
  Future<List<Komoditas>> getKomoditas() async => [
        Komoditas(
          id: 20,
          name: 'Gabah Kering Panen',
          grade: 'A',
          isActive: true,
          kategori: 'padi',
        ),
      ];

  @override
  Future<Map<String, dynamic>> getLaporanList({
    int page = 1,
    String? status,
    int? wilayahId,
    int? komoditasId,
    String? search,
    String? tanggalDari,
    String? tanggalSampai,
  }) async {
    return {
      'success': true,
      'data': seededLaporan.map((e) => e.toJson()).toList(),
    };
  }

  @override
  Future<int> getNotificationsUnreadCount() async => 2;
}

void main() {
  setUpAll(() async {
    await initializeDateFormatting('id_ID', null);
  });

  test('Wilayah.displayName menyertakan kabupaten dan formattedHarga memakai pemisah ribuan titik', () {
    final w = Wilayah(
      id: 1,
      desa: 'Sukamaju',
      kecamatan: 'Cianjur',
      kabupaten: 'Kab. Cianjur',
      provinsi: 'Jawa Barat',
      isActive: true,
    );
    expect(w.displayName, 'Sukamaju - Cianjur, Kab. Cianjur');

    final l = LaporanHarga(
      userId: 1,
      wilayahId: 1,
      komoditasId: 1,
      harga: 7500,
      satuan: 'kg',
      tanggalPanen: DateTime(2026, 11, 15),
      status: 'pending',
    );
    expect(l.formattedHarga, 'Rp 7.500/kg');
  });

  testWidgets('DashboardScreen mempertahankan filter saat kosong dan menampilkan banner offline di lebar 320px', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(320, 640);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);

    SharedPreferences.setMockInitialValues({
      'api_token': 'offline-tok',
      'user': jsonEncode({
        'id': 1,
        'name': 'Petani Uji',
        'email': 'petani@example.com',
        'role': 'petani',
        'status': 'active',
      }),
    });

    final api = _MockLaporanApiService();
    final auth = AuthProvider(api);
    await auth.init();

    // Simulasikan sesi offline lewat ApiService gagal jaringan
    final offlineApi = _MockLaporanApiService()..failProfileWithNetwork = true;
    final offlineAuth = AuthProvider(offlineApi);
    await offlineAuth.init();

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          Provider<ApiService>.value(value: api),
          ChangeNotifierProvider<AuthProvider>.value(value: offlineAuth),
          ChangeNotifierProvider<LaporanProvider>(create: (_) => LaporanProvider(api)),
          ChangeNotifierProvider<NotificationProvider>(create: (_) => NotificationProvider(api)),
          ChangeNotifierProvider<PenyetoranProvider>(create: (_) => PenyetoranProvider(api)),
        ],
        child: const MaterialApp(home: DashboardScreen()),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Cari komoditas atau catatan...'), findsOneWidget);
    expect(find.text('Belum Ada Laporan'), findsOneWidget);
    expect(find.text('Mode offline - menampilkan profil tersimpan'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('CreateLaporanScreen mode edit mengisi dropdown wilayah dan komoditas serta header modal', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final api = _MockLaporanApiService();

    final editing = LaporanHarga(
      id: 99,
      userId: 1,
      wilayahId: 10,
      komoditasId: 20,
      harga: 6800,
      satuan: 'kg',
      tanggalPanen: DateTime(2026, 11, 10),
      status: 'pending',
    );

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          Provider<ApiService>.value(value: api),
          ChangeNotifierProvider<LaporanProvider>(create: (_) => LaporanProvider(api)),
        ],
        child: MaterialApp(
          home: Scaffold(
            body: CreateLaporanScreen(editingLaporan: editing, isModal: true),
          ),
        ),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Edit Laporan'), findsOneWidget);
    expect(find.byIcon(Icons.close), findsOneWidget);
    expect(find.text('Sukamaju - Cianjur, Kab. Cianjur'), findsOneWidget);
    expect(find.text('Gabah Kering Panen (A)'), findsOneWidget);
    expect(find.text('6800'), findsOneWidget);
  });

  testWidgets('LaporanDetailScreen merender rapi pada lebar 320px tanpa overflow', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(320, 640);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);

    SharedPreferences.setMockInitialValues({});
    final api = _MockLaporanApiService();
    final laporan = LaporanHarga(
      id: 5,
      userId: 1,
      wilayahId: 10,
      komoditasId: 20,
      harga: 7200,
      satuan: 'kg',
      tanggalPanen: DateTime(2026, 11, 14),
      tanggalLaporan: DateTime(2026, 11, 15),
      status: 'rejected',
      rejectionReason: 'Foto kurang jelas',
      komoditas: {'name': 'Gabah Kering Panen Super Panjang', 'grade': 'A'},
      wilayah: {'desa': 'Sukamaju', 'kecamatan': 'Cianjur', 'kabupaten': 'Kab. Cianjur'},
    );

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<LaporanProvider>(create: (_) => LaporanProvider(api)),
        ],
        child: MaterialApp(home: LaporanDetailScreen(laporan: laporan)),
      ),
    );

    await tester.pumpAndSettle();
    expect(find.text('Ditolak'), findsOneWidget);
    expect(find.text('Foto kurang jelas'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
