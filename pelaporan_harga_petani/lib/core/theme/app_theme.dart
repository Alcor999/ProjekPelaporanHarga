import 'package:flutter/material.dart';

class AppColors {
  // Primary colors
  static const Color primary = Color(0xFF2E7D32);
  static const Color primaryLight = Color(0xFF66BB6A);
  static const Color primaryDark = Color(0xFF1B5E20);

  // Secondary colors
  static const Color accent = Color(0xFFFFA726);
  static const Color accentLight = Color(0xFFFFB74D);

  // Neutral colors
  static const Color white = Color(0xFFFFFFFF);
  static const Color black = Color(0xFF000000);
  static const Color grey100 = Color(0xFFF5F5F5);
  static const Color grey200 = Color(0xFFEEEEEE);
  static const Color grey300 = Color(0xFFE0E0E0);
  static const Color grey400 = Color(0xFFBDBDBD);
  static const Color grey500 = Color(0xFF757575);
  static const Color grey600 = Color(0xFF616161);

  // Status colors (WCAG AA >= 4.5:1)
  static const Color success = Color(0xFF1B5E20);
  static const Color error = Color(0xFFC62828);
  static const Color warning = Color(0xFFB45309);
  static const Color warningSurface = Color(0xFFFFF3CD);
  static const Color warningInk = Color(0xFF5D4037);
  static const Color info = Color(0xFF0277BD);

  // Background
  static const Color background = Color(0xFFFAFAFA);
  static const Color surface = Color(0xFFFFFFFF);
}

class AppTextStyles {
  static const headlineLarge = TextStyle(
    fontSize: 32,
    fontWeight: FontWeight.bold,
    color: Color(0xFF1B5E20),
  );

  static const headlineMedium = TextStyle(
    fontSize: 28,
    fontWeight: FontWeight.bold,
    color: Color(0xFF1B5E20),
  );

  static const headlineSmall = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.bold,
    color: Color(0xFF1B5E20),
  );

  static const titleLarge = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w600,
    color: Color(0xFF333333),
  );

  static const titleMedium = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: Color(0xFF333333),
  );

  static const bodyLarge = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w400,
    color: Color(0xFF666666),
  );

  static const bodyMedium = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w400,
    color: Color(0xFF666666),
  );

  static const bodySmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    color: Color(0xFF616161),
  );

  static const labelLarge = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w600,
    color: Color(0xFF333333),
  );

  static const labelSmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w600,
    color: Color(0xFF666666),
  );
}

class AppSpacing {
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 16;
  static const double lg = 24;
  static const double xl = 32;
  static const double xxl = 48;
}

class AppBorderRadius {
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double full = 999;
}

