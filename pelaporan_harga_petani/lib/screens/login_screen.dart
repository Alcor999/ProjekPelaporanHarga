import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme/app_theme.dart';
import '../providers/auth_provider.dart';
import '../widgets/buttons.dart';
import '../widgets/custom_text_field.dart';
import '../widgets/common_widgets.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  late TextEditingController _emailController;
  late TextEditingController _passwordController;
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    _emailController = TextEditingController();
    _passwordController = TextEditingController();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _handleLogin() async {
    if (_formKey.currentState?.validate() ?? false) {
      final authProvider = context.read<AuthProvider>();
      final success = await authProvider.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      if (success && mounted) {
        if (Navigator.of(context).canPop()) {
          Navigator.of(context).popUntil((route) => route.isFirst);
        }
      } else if (!success && mounted) {
        showErrorSnackBar(context, authProvider.errorMessage ?? 'Login gagal');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(height: MediaQuery.of(context).size.height * 0.08),
                // Header
                Text(
                  'Selamat Datang',
                  style: AppTextStyles.headlineLarge,
                ),
                SizedBox(height: AppSpacing.sm),
                Text(
                  'Masuk ke akun petani Anda untuk melaporkan harga padi',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.grey600,
                  ),
                ),
                SizedBox(height: AppSpacing.xl),

                // Email Field
                CustomTextField(
                  label: 'Email',
                  hint: 'nama@email.com',
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  prefixIcon: const Icon(Icons.email_outlined),
                  validator: (value) {
                    if (value?.isEmpty ?? true) {
                      return 'Email tidak boleh kosong';
                    }
                    if (!value!.contains('@')) {
                      return 'Email tidak valid';
                    }
                    return null;
                  },
                ),
                SizedBox(height: AppSpacing.lg),

                // Password Field
                CustomTextField(
                  label: 'Password',
                  hint: 'Masukkan password Anda',
                  controller: _passwordController,
                  obscureText: true,
                  prefixIcon: const Icon(Icons.lock_outlined),
                  validator: (value) {
                    if (value?.isEmpty ?? true) {
                      return 'Password tidak boleh kosong';
                    }
                    return null;
                  },
                ),
                SizedBox(height: AppSpacing.lg),

                // Login Button
                Consumer<AuthProvider>(
                  builder: (context, authProvider, _) {
                    return PrimaryButton(
                      label: 'Masuk',
                      isLoading: authProvider.isLoading,
                      onPressed: _handleLogin,
                    );
                  },
                ),

                SizedBox(height: AppSpacing.lg),

                // Register Link
                Center(
                  child: Wrap(
                    alignment: WrapAlignment.center,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      Text(
                        'Belum punya akun? ',
                        style: AppTextStyles.bodyMedium,
                      ),
                      TextButton(
                        style: TextButton.styleFrom(
                          minimumSize: const Size(44, 44),
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppSpacing.sm,
                            vertical: AppSpacing.sm,
                          ),
                        ),
                        onPressed: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const RegisterScreen(),
                            ),
                          );
                        },
                        child: Text(
                          'Daftar di sini',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.primary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                SizedBox(height: AppSpacing.xl * 2),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

