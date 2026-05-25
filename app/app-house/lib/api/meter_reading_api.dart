import 'package:dio/dio.dart';

class MeterReadingApi {
  final Dio _dio;

  MeterReadingApi(this._dio);

  Future<Map<String, dynamic>> create({
    required int roomId,
    required String period,
    required int electricityReading,
    required int waterReading,
  }) async {
    final response = await _dio.post(
      '/meter-readings',
      data: {
        'room_id': roomId,
        'period': period,
        'electricity_reading': electricityReading,
        'water_reading': waterReading,
      },
    );

    return Map<String, dynamic>.from(response.data as Map);
  }
}

