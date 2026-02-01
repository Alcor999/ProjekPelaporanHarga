class ApiConstants {
  // Backend API base URL
  // Sesuaikan dengan URL backend Laravel Anda
  // For Android Emulator: http://10.0.2.2:8000/api
  // For iPhone Simulator: http://localhost:8000/api
  // For physical device on LAN: http://192.168.x.x:8000/api
  // IMPORTANT: Untuk physical device, gunakan IP lokal komputer Anda, bukan localhost!
  // Pastikan Laravel berjalan dengan: php artisan serve --host=0.0.0.0 --port=8000
  static const String baseUrl = 'http://192.168.100.221:8000/api';
  static String get storageUrl => baseUrl.replaceAll('/api', '');

  // Auth endpoints
  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String profile = '/profile';

  // Referensi endpoints
  static const String wilayah = '/ref/wilayah';
  static const String komoditas = '/ref/komoditas';

  // Laporan endpoints
  static const String laporan = '/petani/laporan';
  static const String laporanDetail = '/petani/laporan';

  // Public (no auth)
  static const String hargaTerkini = '/public/harga-terkini';
  static const String hargaTren = '/public/harga-tren';

  // Suppliers (public)
  static const String suppliers = '/suppliers';
  static String supplierDetail(int id) => '/suppliers/$id';

  // Penyetoran (petani)
  static const String penyetoran = '/petani/penyetoran';
  static String penyetoranDetail(int id) => '/petani/penyetoran/$id';
  static String supplierRating(int id) => '/petani/suppliers/$id/rating';

  // Notifications
  static const String notifications = '/petani/notifications';
  static const String notificationsUnreadCount = '/petani/notifications/unread-count';
  static String notificationRead(int id) => '/petani/notifications/$id/read';
  static const String notificationsReadAll = '/petani/notifications/read-all';

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
