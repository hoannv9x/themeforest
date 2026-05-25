class Room {
  final int id;
  final int boardingHouseId;
  final String name;
  final String status;
  final int rentAmount;
  final int electricityRate;
  final int waterRate;
  final int wifiFee;
  final int trashFee;
  final int parkingFee;

  const Room({
    required this.id,
    required this.boardingHouseId,
    required this.name,
    required this.status,
    required this.rentAmount,
    required this.electricityRate,
    required this.waterRate,
    required this.wifiFee,
    required this.trashFee,
    required this.parkingFee,
  });

  factory Room.fromJson(Map<String, dynamic> json) {
    return Room(
      id: (json['id'] as num).toInt(),
      boardingHouseId: (json['boarding_house_id'] as num).toInt(),
      name: (json['name'] as String?) ?? '',
      status: (json['status'] as String?) ?? 'vacant',
      rentAmount: (json['rent_amount'] as num?)?.toInt() ?? 0,
      electricityRate: (json['electricity_rate'] as num?)?.toInt() ?? 0,
      waterRate: (json['water_rate'] as num?)?.toInt() ?? 0,
      wifiFee: (json['wifi_fee'] as num?)?.toInt() ?? 0,
      trashFee: (json['trash_fee'] as num?)?.toInt() ?? 0,
      parkingFee: (json['parking_fee'] as num?)?.toInt() ?? 0,
    );
  }
}

