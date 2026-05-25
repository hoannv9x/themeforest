import 'package:app_house/api/auth_api.dart';
import 'package:app_house/core/api_error.dart';
import 'package:app_house/core/app_services.dart';
import 'package:app_house/screens/home/home_page.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class LoginRegisterPage extends StatefulWidget {
  final AppServices services;

  const LoginRegisterPage({super.key, required this.services});

  @override
  State<LoginRegisterPage> createState() => _LoginRegisterPageState();
}

class _LoginRegisterPageState extends State<LoginRegisterPage>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController = TabController(
    length: 2,
    vsync: this,
  );

  final _loginPhone = TextEditingController();
  final _loginPassword = TextEditingController();

  final _registerName = TextEditingController();
  final _registerPhone = TextEditingController();
  final _registerEmail = TextEditingController();
  final _registerPassword = TextEditingController();
  String _registerRole = 'landlord';

  bool _loading = false;

  AuthApi get _authApi => AuthApi(widget.services.apiClient.dio);

  @override
  void dispose() {
    _tabController.dispose();
    _loginPhone.dispose();
    _loginPassword.dispose();
    _registerName.dispose();
    _registerPhone.dispose();
    _registerEmail.dispose();
    _registerPassword.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Smart Boarding House'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Đăng nhập'),
            Tab(text: 'Đăng ký'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [_buildLogin(), _buildRegister()],
      ),
    );
  }

  Widget _buildLogin() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          TextField(
            controller: _loginPhone,
            keyboardType: TextInputType.phone,
            decoration: const InputDecoration(labelText: 'Số điện thoại'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _loginPassword,
            obscureText: true,
            decoration: const InputDecoration(labelText: 'Mật khẩu'),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: _loading ? null : _handleLogin,
              child: _loading
                  ? const SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(),
                    )
                  : const Text('Đăng nhập'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRegister() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: ListView(
        children: [
          TextField(
            controller: _registerName,
            decoration: const InputDecoration(labelText: 'Họ tên'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _registerPhone,
            keyboardType: TextInputType.phone,
            decoration: const InputDecoration(labelText: 'Số điện thoại'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _registerEmail,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(labelText: 'Email (tuỳ chọn)'),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _registerRole,
            items: const [
              DropdownMenuItem(value: 'landlord', child: Text('Chủ trọ')),
              DropdownMenuItem(value: 'staff', child: Text('Nhân viên')),
              DropdownMenuItem(value: 'tenant', child: Text('Người thuê')),
            ],
            onChanged: _loading
                ? null
                : (v) {
                    if (v == null) return;
                    setState(() => _registerRole = v);
                  },
            decoration: const InputDecoration(labelText: 'Vai trò'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _registerPassword,
            obscureText: true,
            decoration: const InputDecoration(
              labelText: 'Mật khẩu (>= 8 ký tự)',
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: _loading ? null : _handleRegister,
              child: _loading
                  ? const SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(),
                    )
                  : const Text('Tạo tài khoản'),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _handleLogin() async {
    setState(() => _loading = true);
    try {
      final payload = await _authApi.login(
        phone: _loginPhone.text.trim(),
        password: _loginPassword.text,
      );

      final token = payload['token'] as String?;
      if (token == null || token.isEmpty) {
        throw ApiError('Không nhận được token từ server.');
      }

      await widget.services.tokenStore.writeToken(token);
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => HomePage(services: widget.services)),
      );
    } on DioException catch (e) {
      _showError(_extractMessage(e));
    } on ApiError catch (e) {
      _showError(e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _handleRegister() async {
    setState(() => _loading = true);
    try {
      final payload = await _authApi.register(
        name: _registerName.text.trim(),
        phone: _registerPhone.text.trim(),
        role: _registerRole,
        email: _registerEmail.text.trim().isEmpty
            ? null
            : _registerEmail.text.trim(),
        password: _registerPassword.text,
      );

      final token = payload['token'] as String?;
      if (token == null || token.isEmpty) {
        throw ApiError('Không nhận được token từ server.');
      }

      await widget.services.tokenStore.writeToken(token);
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => HomePage(services: widget.services)),
      );
    } on DioException catch (e) {
      _showError(_extractMessage(e));
    } on ApiError catch (e) {
      _showError(e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  String _extractMessage(DioException e) {
    final error = e.error;
    if (error is ApiError) return error.message;
    if (e.message != null && e.message!.isNotEmpty) return e.message!;
    return 'Có lỗi xảy ra.';
  }

  void _showError(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }
}
