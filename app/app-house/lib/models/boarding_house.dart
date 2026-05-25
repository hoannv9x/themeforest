class BoardingHouse {
  final int id;
  final String name;
  final String? address;
  final String? note;

  const BoardingHouse({
    required this.id,
    required this.name,
    this.address,
    this.note,
  });

  factory BoardingHouse.fromJson(Map<String, dynamic> json) {
    return BoardingHouse(
      id: (json['id'] as num).toInt(),
      name: (json['name'] as String?) ?? '',
      address: json['address'] as String?,
      note: json['note'] as String?,
    );
  }
}

