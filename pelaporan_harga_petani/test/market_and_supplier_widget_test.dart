import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:pelaporan_harga_petani/core/exceptions/api_exception.dart';
import 'package:pelaporan_harga_petani/models/komoditas_model.dart';
import 'package:pelaporan_harga_petani/models/wilayah_model.dart';
import 'package:pelaporan_harga_petani/providers/harga_publik_provider.dart';
import 'package:pelaporan_harga_petani/providers/laporan_provider.dart';
import 'package:pelaporan_harga_petani/providers/notification_provider.dart';
import 'package:pelaporan_harga_petani/providers/penyetoran_provider.dart';
import 'package:pelaporan_harga_petani/providers/supplier_provider.dart';
import 'package:pelaporan_harga_petani/screens/harga_publik_screen.dart';
import 'package:pelaporan_harga_petani/screens/notifications_screen.dart';
import 'package:pelaporan_harga_petani/screens/penyetoran_list_screen.dart';
import 'package:pelaporan_harga_petani/screens/supplier_detail_screen.dart';
import 'package:pelaporan_harga_petani/screens/supplier_list_screen.dart';
import 'package:pelaporan_harga_petani/services/api_service.dart';

class _MockMarketApiService extends ApiService {
  bool failNotifications = false;
  bool failSuppliers = false;
  int? capturedRatedPenyetoranId;
  int initRefCalls = 0;

  @override
  Future<List<Wilayah>> getWilayah() async {
    initRefCalls++;
    return [];
  }

  @override
  Future<List<Komoditas>> getKomoditas() async => [];

  @override
  Future<Map<String, dynamic>> getHargaTerkini({int? wilayahId, int? komoditasId}) async {
    return {
      'success': true,
      'data': [
        {
          'komoditas': {'id': 1, 'name': 'Gabah Kering', 'grade': 'A', 'kategori': 'padi'},
          'satuan': 'kwintal',
          'harga_terendah': 0,
          'harga_tertinggi': 650000,
          'total_wilayah': 2,
          'wilayah_data': [
            {
              'wilayah': {'kabupaten': 'Kab. Cianjur', 'kecamatan': 'Cianjur'},
              'satuan': 'kwintal',
              'harga_terbaru': 0,
              'perubahan': 0,
              'perubahan_persen': 0,
              'tanggal_update': '2026-11-15',
              'jumlah_laporan': 1,
            },
            {
              'wilayah': {'kabupaten': 'Kab. Bandung', 'kecamatan': 'Soreang'},
              'satuan': 'kwintal',
              'harga_terbaru': 650000,
              'perubahan': 10000,
              'perubahan_persen': 1.6,
              'tanggal_update': '2026-11-15',
              'jumlah_laporan': 3,
            },
          ],
        },
      ],
    };
  }

  @override
  Future<List<Map<String, dynamic>>> getSuppliers({int? wilayahId, int? komoditasId}) async {
    if (failSuppliers) {
      throw NetworkException(message: 'Gagal memuat supplier');
    }
    return [
      {
        'id': 3,
        'name': 'UD Sumber Tani',
        'avg_rating': 4.5,
        'ratings_count': 4,
        'harga': [
          {
            'komoditas_id': 1,
            'harga_per_satuan': 6500,
            'satuan': 'kg',
            'komoditas': {'name': 'Gabah'},
          },
        ],
      },
    ];
  }

  @override
  Future<Map<String, dynamic>?> getSupplierDetail(int id) async {
    return {
      'id': id,
      'name': 'UD Tanpa Harga',
      'harga': <Map<String, dynamic>>[],
      'jadwal': <Map<String, dynamic>>[],
      'ratings': <Map<String, dynamic>>[],
      'avg_rating': 0,
      'ratings_count': 0,
    };
  }

  @override
  Future<Map<String, dynamic>> getPenyetoranList({int page = 1, String? status}) async {
    return {
      'total': 2,
      'data': [
        {
          'id': 101,
          'supplier_id': 3,
          'supplier': {'id': 3, 'name': 'UD Sumber Tani'},
          'komoditas': {'name': 'Gabah'},
          'jumlah': 50,
          'satuan': 'kg',
          'total_nilai': 325000,
          'tanggal': '2026-11-15',
          'status': 'completed',
          'has_rating': false,
        },
        {
          'id': 102,
          'supplier_id': 3,
          'supplier': {'id': 3, 'name': 'UD Lain'},
          'komoditas': {'name': 'Jagung'},
          'jumlah': 20,
          'satuan': 'kg',
          'total_nilai': 100000,
          'tanggal': '2026-11-15',
          'status': 'cancelled',
          'has_rating': false,
        },
      ],
    };
  }

  @override
  Future<void> submitSupplierRating(int supplierId, {required int rating, String? ulasan, int? penyetoranId}) async {
    capturedRatedPenyetoranId = penyetoranId;
  }

  @override
  Future<Map<String, dynamic>> getNotifications({int page = 1, bool unreadOnly = false}) async {
    if (failNotifications) {
      throw NetworkException(message: 'Gagal memuat notifikasi');
    }
    return {'success': true, 'data': [], 'unread_count': 0};
  }
}

void main() {
  setUpAll(() async {
    await initializeDateFormatting('id_ID', null);
  });

  testWidgets('HargaPublikScreen merender di lebar 320px dengan satuan dinamis tanpa loop rebuild', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(320, 640);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);

    SharedPreferences.setMockInitialValues({});
    final api = _MockMarketApiService();

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<LaporanProvider>(create: (_) => LaporanProvider(api)),
          ChangeNotifierProvider<HargaPublikProvider>(create: (_) => HargaPublikProvider(api)),
        ],
        child: const MaterialApp(home: HargaPublikScreen()),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Rp 0/kwintal'), findsOneWidget);
    expect(find.text('Rp 650.000/kwintal'), findsOneWidget);
    expect(api.initRefCalls, 1);
    expect(tester.takeException(), isNull);
  });

  testWidgets('SupplierListScreen menampilkan Filter Supplier dan tombol Coba Lagi saat gagal', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final api = _MockMarketApiService()..failSuppliers = true;

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<LaporanProvider>(create: (_) => LaporanProvider(api)),
          ChangeNotifierProvider<SupplierProvider>(create: (_) => SupplierProvider(api)),
        ],
        child: const MaterialApp(home: SupplierListScreen()),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Filter Supplier'), findsOneWidget);
    expect(find.text('Filter live'), findsNothing);
    expect(find.text('Coba Lagi'), findsOneWidget);
  });

  testWidgets('PenyetoranListScreen mendukung filter Dibatalkan dan tombol Beri Rating terhubung penyetoranId', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final api = _MockMarketApiService();

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<PenyetoranProvider>(create: (_) => PenyetoranProvider(api)),
        ],
        child: const MaterialApp(home: PenyetoranListScreen()),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('SELESAI'), findsOneWidget);
    expect(find.text('DIBATALKAN'), findsOneWidget);
    expect(find.text('Beri Rating'), findsOneWidget);

    await tester.tap(find.text('Beri Rating'));
    await tester.pumpAndSettle();

    await tester.tap(find.byTooltip('5 bintang'));
    await tester.pump();
    await tester.tap(find.text('Kirim Rating'));
    await tester.pumpAndSettle();

    expect(api.capturedRatedPenyetoranId, 101);
  });

  testWidgets('SupplierDetailScreen menyembunyikan form penyetoran saat harga supplier kosong', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final api = _MockMarketApiService();

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<SupplierProvider>(create: (_) => SupplierProvider(api)),
          ChangeNotifierProvider<PenyetoranProvider>(create: (_) => PenyetoranProvider(api)),
        ],
        child: const MaterialApp(home: SupplierDetailScreen(supplierId: 9)),
      ),
    );

    await tester.pumpAndSettle();
    expect(find.text('Supplier ini belum menetapkan harga pembelian aktif.'), findsOneWidget);
    expect(find.text('Catat Penyetoran'), findsNothing);
  });

  testWidgets('NotificationsScreen menampilkan pesan error dan tombol Coba Lagi saat gagal', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final api = _MockMarketApiService()..failNotifications = true;

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<NotificationProvider>(create: (_) => NotificationProvider(api)),
        ],
        child: const MaterialApp(home: NotificationsScreen()),
      ),
    );

    await tester.pumpAndSettle();
    expect(find.text('Gagal memuat notifikasi'), findsOneWidget);
    expect(find.text('Coba Lagi'), findsOneWidget);
  });
}
