import 'package:app_house/models/boarding_house.dart';
import 'package:dio/dio.dart';

class BoardingHouseApi {
  final Dio _dio;

  BoardingHouseApi(this._dio);

  Future<List<BoardingHouse>> list() async {
    final response = await _dio.get('/boarding-houses');
    final data = Map<String, dynamic>.from(response.data as Map);
    final list = (data['data'] as List).cast<dynamic>();
    return list.map((e) => BoardingHouse.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }

  Future<BoardingHouse> create({
    required String name,
    String? address,
    String? note,
  }) async {
    final response = await _dio.post(
      '/boarding-houses',
      data: {
        'name': name,
        'address': address,
        'note': note,
      },
    );

    final data = Map<String, dynamic>.from(response.data as Map);
    return BoardingHouse.fromJson(Map<String, dynamic>.from(data['data'] as Map));
  }
}

