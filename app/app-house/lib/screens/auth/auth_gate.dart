import 'package:app_house/api/auth_api.dart';
import 'package:app_house/core/app_services.dart';
import 'package:app_house/screens/auth/login_register_page.dart';
import 'package:app_house/screens/home/home_page.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class AuthGate extends StatefulWidget {
  const AuthGate({super.key});

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  late final Future<AppServices> _servicesFuture = AppServices.create();

  @override
  Widget build(BuildContext context) {
    return FutureBuilder(
      future: _servicesFuture,
      builder: (context, snapshot) {
        if (!snapshot.hasData) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        final services = snapshot.data!;

        return FutureBuilder(
          future: _bootstrap(services),
          builder: (context, authSnapshot) {
            if (authSnapshot.connectionState != ConnectionState.done) {
              return const Scaffold(
                body: Center(child: CircularProgressIndicator()),
              );
            }

            final isAuthenticated = authSnapshot.data == true;

            return isAuthenticated
                ? HomePage(services: services)
                : LoginRegisterPage(services: services);
          },
        );
      },
    );
  }

  Future<bool> _bootstrap(AppServices services) async {
    final token = await services.tokenStore.readToken();
    if (token == null || token.isEmpty) {
      return false;
    }

    try {
      final api = AuthApi(services.apiClient.dio);
      await api.me();
      return true;
    } on DioException {
      await services.tokenStore.deleteToken();
      return false;
    }
  }
}
