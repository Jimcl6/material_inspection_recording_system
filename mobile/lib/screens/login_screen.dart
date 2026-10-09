import 'package:flutter/material.dart';
import '../core/theme/app_colors.dart';
import '../providers/auth_controller.dart';
import '../providers/server_config_controller.dart';
import 'server_config_screen.dart';

class LoginScreen extends StatefulWidget {
  final AuthController authController;
  final ServerConfigController configController;
  final VoidCallback onLoginSuccess;

  const LoginScreen({
    super.key,
    required this.authController,
    required this.configController,
    required this.onLoginSuccess,
  });

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    final remembered = widget.authController.storage.getRememberedEmail();
    if (remembered != null && remembered.isNotEmpty) {
      _emailController.text = remembered;
    } else {
      _emailController.text = 'admin@example.com';
      _passwordController.text = 'password';
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _submit() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter your email/ID and password.'),
          backgroundColor: AppColors.amber700,
        ),
      );
      return;
    }

    final success = await widget.authController.login(
      email: email,
      password: password,
    );

    if (success && mounted) {
      widget.onLoginSuccess();
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = widget.authController;
    final server = widget.configController;

    return Scaffold(
      backgroundColor: AppColors.gray100,
      body: Center(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 24),
            child: Container(
              constraints: const BoxConstraints(maxWidth: 960),
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.gray200),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x1F000000),
                    blurRadius: 30,
                    offset: Offset(0, 10),
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Left Side: Brand & Factory Identity
                  Expanded(
                    flex: 4,
                    child: Container(
                      padding: const EdgeInsets.all(40),
                      decoration: const BoxDecoration(
                        color: AppColors.indigo600,
                        borderRadius: BorderRadius.horizontal(left: Radius.circular(20)),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.precision_manufacturing_rounded,
                              color: AppColors.white,
                              size: 36,
                            ),
                          ),
                          const SizedBox(height: 24),
                          const Text(
                            'Material Inspection\nRecording System',
                            style: TextStyle(
                              fontSize: 26,
                              fontWeight: FontWeight.bold,
                              color: AppColors.white,
                              height: 1.2,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            'Internal tablet workstation for high-precision manufacturing inspection, quality audits, and Bluetooth barcode scanning.',
                            style: TextStyle(
                              fontSize: 14,
                              color: AppColors.white.withValues(alpha: 0.85),
                              height: 1.4,
                            ),
                          ),
                          const SizedBox(height: 36),
                          Row(
                            children: [
                              Icon(Icons.bluetooth_connected, color: AppColors.white.withValues(alpha: 0.8), size: 18),
                              const SizedBox(width: 8),
                              Text(
                                'Wireless Scanner HID Ready',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.white.withValues(alpha: 0.9),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Icon(Icons.lock_outline, color: AppColors.white.withValues(alpha: 0.8), size: 18),
                              const SizedBox(width: 8),
                              Text(
                                'Sanctum Bearer LAN Protection',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.white.withValues(alpha: 0.9),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Right Side: Sign In Form
                  Expanded(
                    flex: 5,
                    child: Padding(
                      padding: const EdgeInsets.all(40),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Server Config Shortcut Header
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Sign In',
                                style: Theme.of(context).textTheme.headlineSmall,
                              ),
                              TextButton.icon(
                                onPressed: () => ServerConfigScreen.showAsDialog(context, server),
                                icon: const Icon(Icons.settings, size: 16),
                                label: const Text('Server Settings'),
                                style: TextButton.styleFrom(
                                  foregroundColor: AppColors.indigo600,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Enter your employee credentials to continue',
                            style: TextStyle(color: AppColors.gray500, fontSize: 13),
                          ),
                          const SizedBox(height: 20),

                          // Error Banner
                          AnimatedBuilder(
                            animation: auth,
                            builder: (context, _) {
                              if (auth.errorMessage == null) return const SizedBox.shrink();
                              return Container(
                                margin: const EdgeInsets.only(bottom: 16),
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: AppColors.red50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: AppColors.red500.withValues(alpha: 0.4)),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.error_outline, color: AppColors.red600, size: 20),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(
                                        auth.errorMessage!,
                                        style: const TextStyle(
                                          color: AppColors.red700,
                                          fontSize: 13,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),

                          // Email / ID Field
                          const Text(
                            'Email or Employee ID',
                            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _emailController,
                            decoration: const InputDecoration(
                              hintText: 'e.g. admin@example.com or EMP-101',
                              prefixIcon: Icon(Icons.person_outline, color: AppColors.gray400),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Password Field
                          const Text(
                            'Password',
                            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _passwordController,
                            obscureText: _obscurePassword,
                            decoration: InputDecoration(
                              hintText: 'Enter password',
                              prefixIcon: const Icon(Icons.lock_outline, color: AppColors.gray400),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                  color: AppColors.gray400,
                                ),
                                onPressed: () {
                                  setState(() {
                                    _obscurePassword = !_obscurePassword;
                                  });
                                },
                              ),
                            ),
                            onSubmitted: (_) => _submit(),
                          ),
                          const SizedBox(height: 24),

                          // Submit Button
                          AnimatedBuilder(
                            animation: auth,
                            builder: (context, _) {
                              return ElevatedButton(
                                onPressed: auth.isLoading ? null : _submit,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.indigo600,
                                  minimumSize: const Size(0, 50),
                                ),
                                child: auth.isLoading
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: AppColors.white,
                                        ),
                                      )
                                    : const Text(
                                        'Sign In to Tablet',
                                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                                      ),
                              );
                            },
                          ),

                          const SizedBox(height: 16),

                          // Quick Autofill for testing
                          Center(
                            child: TextButton(
                              onPressed: () {
                                setState(() {
                                  _emailController.text = 'admin@example.com';
                                  _passwordController.text = 'password';
                                });
                              },
                              child: const Text(
                                'Use Default Admin Credentials (admin@example.com)',
                                style: TextStyle(fontSize: 12, color: AppColors.gray500),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
