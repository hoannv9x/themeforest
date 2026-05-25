import 'package:app_house/core/api_error.dart';
import 'package:app_house/core/app_config.dart';
import 'package:app_house/core/token_store.dart';
import 'package:dio/dio.dart';

class ApiClient {
  final Dio dio;

  ApiClient._(this.dio);

  static Future<ApiClient> create({TokenStore? tokenStore}) async {
    final store = tokenStore ?? TokenStore();

    final dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {'Accept': 'application/json'},
      ),
    );

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await store.readToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (e, handler) {
          handler.reject(_normalizeError(e));
        },
      ),
    );

    return ApiClient._(dio);
  }

  static DioException _normalizeError(DioException e) {
    final statusCode = e.response?.statusCode;
    final data = e.response?.data;

    String message = 'Có lỗi xảy ra.';

    if (data is Map<String, dynamic>) {
      final maybeMessage = data['message'];
      if (maybeMessage is String && maybeMessage.isNotEmpty) {
        message = maybeMessage;
      }

      final errors = data['errors'];
      if (errors is Map<String, dynamic> && errors.isNotEmpty) {
        final firstField = errors.entries.first;
        final firstValue = firstField.value;
        if (firstValue is List &&
            firstValue.isNotEmpty &&
            firstValue.first is String) {
          message = firstValue.first as String;
        }
      }
    } else if (e.message != null && e.message!.isNotEmpty) {
      message = e.message!;
    }

    return DioException(
      requestOptions: e.requestOptions,
      response: e.response,
      type: e.type,
      error: ApiError(message, statusCode: statusCode),
      message: message,
    );
  }
}
