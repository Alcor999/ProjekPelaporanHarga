import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:pelaporan_harga_petani/main.dart';
import 'package:pelaporan_harga_petani/providers/auth_provider.dart';
import 'package:pelaporan_harga_petani/providers/harga_publik_provider.dart';
import 'package:pelaporan_harga_petani/providers/laporan_provider.dart';
import 'package:pelaporan_harga_petani/providers/notification_provider.dart';
import 'package:pelaporan_harga_petani/providers/penyetoran_provider.dart';
import 'package:pelaporan_harga_petani/providers/supplier_provider.dart';
import 'package:pelaporan_harga_petani/screens/login_screen.dart';
import 'package:pelaporan_harga_petani/screens/splash_screen.dart';
import 'package:pelaporan_harga_petani/services/api_service.dart';

void main() {
  testWidgets('MyApp menampilkan SplashScreen lalu LoginScreen saat belum ada token', (WidgetTester tester) async {
    SharedPreferences.setMockInitialValues({});
    final apiService = ApiService();

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          Provider<ApiService>.value(value: apiService),
          ChangeNotifierProvider<AuthProvider>(create: (_) => AuthProvider(apiService)),
          ChangeNotifierProvider<LaporanProvider>(create: (_) => LaporanProvider(apiService)),
          ChangeNotifierProvider<NotificationProvider>(create: (_) => NotificationProvider(apiService)),
          ChangeNotifierProvider<HargaPublikProvider>(create: (_) => HargaPublikProvider(apiService)),
          ChangeNotifierProvider<SupplierProvider>(create: (_) => SupplierProvider(apiService)),
          ChangeNotifierProvider<PenyetoranProvider>(create: (_) => PenyetoranProvider(apiService)),
        ],
        child: const MyApp(),
      ),
    );

    expect(find.byType(SplashScreen), findsOneWidget);

    await tester.pumpAndSettle();
    expect(find.byType(LoginScreen), findsOneWidget);
  });
}
