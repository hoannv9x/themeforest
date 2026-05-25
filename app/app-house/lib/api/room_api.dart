import 'package:app_house/models/room.dart';
import 'package:dio/dio.dart';

class RoomApi {
  final Dio _dio;

  RoomApi(this._dio);

  Future<List<Room>> list({int? boardingHouseId}) async {
    final response = await _dio.get(
      '/rooms',
      queryParameters: boardingHouseId == null ? null : {'boarding_house_id': boardingHouseId},
    );
    final data = Map<String, dynamic>.from(response.data as Map);
    final list = (data['data'] as List).cast<dynamic>();
    return list.map((e) => Room.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }

  Future<Room> create({
    required int boardingHouseId,
    required String name,
    required int rentAmount,
    int electricityRate = 0,
    int waterRate = 0,
    int wifiFee = 0,
    int trashFee = 0,
    int parkingFee = 0,
  }) async {
    final response = await _dio.post(
      '/rooms',
      data: {
        'boarding_house_id': boardingHouseId,
        'name': name,
        'rent_amount': rentAmount,
        'electricity_rate': electricityRate,
        'water_rate': waterRate,
        'wifi_fee': wifiFee,
        'trash_fee': trashFee,
        'parking_fee': parkingFee,
      },
    );

    final data = Map<String, dynamic>.from(response.data as Map);
    return Room.fromJson(Map<String, dynamic>.from(data['data'] as Map));
  }
}

