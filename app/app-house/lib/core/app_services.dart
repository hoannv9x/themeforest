import 'package:app_house/core/api_client.dart';
import 'package:app_house/core/token_store.dart';

class AppServices {
  final TokenStore tokenStore;
  final ApiClient apiClient;

  AppServices._({required this.tokenStore, required this.apiClient});

  static Future<AppServices> create() async {
    final tokenStore = TokenStore();
    final apiClient = await ApiClient.create(tokenStore: tokenStore);
    return AppServices._(tokenStore: tokenStore, apiClient: apiClient);
  }
}

