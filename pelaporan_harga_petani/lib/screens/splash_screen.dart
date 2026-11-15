import 'package:flutter/material.dart';
import '../core/theme/app_theme.dart';

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primary,
      body: Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Icon(
                  Icons.grain,
                  size: 50,
                  color: AppColors.primary,
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
              Text(
                'Lapor Harga Panen',
                textAlign: TextAlign.center,
                style: AppTextStyles.headlineMedium.copyWith(
                  color: AppColors.white,
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Text(
                'Memeriksa sesi petani Anda...',
                textAlign: TextAlign.center,
                style: AppTextStyles.bodyMedium.copyWith(
                  color: AppColors.white,
                ),
              ),
              const SizedBox(height: AppSpacing.xl * 2),
              const SizedBox(
                width: 40,
                height: 40,
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(AppColors.white),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

