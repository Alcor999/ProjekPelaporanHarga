import 'package:flutter/material.dart';
import '../core/exceptions/api_exception.dart';
import '../models/notification_model.dart';
import '../services/api_service.dart';

class NotificationProvider extends ChangeNotifier {
  final ApiService _apiService;

  List<NotificationModel> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;
  String? _errorMessage;

  List<NotificationModel> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  NotificationProvider(this._apiService);

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  void reset() {
    _notifications = [];
    _unreadCount = 0;
    _isLoading = false;
    _errorMessage = null;
    notifyListeners();
  }

  Future<void> loadNotifications({int page = 1, bool unreadOnly = false}) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      final res = await _apiService.getNotifications(page: page, unreadOnly: unreadOnly);
      final data = res['data'];
      List<dynamic> list = [];
      if (data is Map && data.containsKey('data')) {
        list = data['data'] ?? [];
      } else if (data is List) {
        list = data;
      }
      _notifications = list.map((e) => NotificationModel.fromJson(Map<String, dynamic>.from(e as Map))).toList();
      _unreadCount = res['unread_count'] ?? 0;
      notifyListeners();
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshUnreadCount() async {
    _unreadCount = await _apiService.getNotificationsUnreadCount();
    notifyListeners();
  }

  Future<bool> markAsRead(int id) async {
    try {
      _errorMessage = null;
      await _apiService.markNotificationAsRead(id);
      final idx = _notifications.indexWhere((n) => n.id == id);
      if (idx >= 0 && !_notifications[idx].isRead) {
        _notifications[idx] = NotificationModel(
          id: _notifications[idx].id,
          type: _notifications[idx].type,
          title: _notifications[idx].title,
          message: _notifications[idx].message,
          isRead: true,
          createdAt: _notifications[idx].createdAt,
        );
        _unreadCount = (_unreadCount - 1).clamp(0, 999);
      }
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> markAllAsRead() async {
    try {
      _errorMessage = null;
      await _apiService.markAllNotificationsAsRead();
      _notifications = _notifications.map((n) => NotificationModel(
        id: n.id,
        type: n.type,
        title: n.title,
        message: n.message,
        isRead: true,
        createdAt: n.createdAt,
      )).toList();
      _unreadCount = 0;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }
}
