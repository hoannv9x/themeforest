import 'package:app_house/api/room_api.dart';
import 'package:app_house/core/api_error.dart';
import 'package:app_house/core/app_services.dart';
import 'package:app_house/models/boarding_house.dart';
import 'package:app_house/models/room.dart';
import 'package:app_house/screens/meter_readings/meter_reading_page.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class RoomsPage extends StatefulWidget {
  final AppServices services;
  final BoardingHouse boardingHouse;

  const RoomsPage({
    super.key,
    required this.services,
    required this.boardingHouse,
  });

  @override
  State<RoomsPage> createState() => _RoomsPageState();
}

class _RoomsPageState extends State<RoomsPage> {
  late final RoomApi _roomApi = RoomApi(widget.services.apiClient.dio);
  late Future<List<Room>> _future = _roomApi.list(
    boardingHouseId: widget.boardingHouse.id,
  );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Phòng - ${widget.boardingHouse.name}')),
      body: FutureBuilder<List<Room>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return Center(child: Text(_errorMessage(snapshot.error)));
          }
          final rooms = snapshot.data ?? const [];
          if (rooms.isEmpty) {
            return const Center(child: Text('Chưa có phòng. Bấm + để tạo.'));
          }

          return ListView.separated(
            itemCount: rooms.length,
            separatorBuilder: (_, _) => const Divider(height: 0),
            itemBuilder: (context, index) {
              final room = rooms[index];
              return ListTile(
                title: Text(room.name),
                subtitle: Text(
                  'Giá: ${room.rentAmount} | Điện: ${room.electricityRate} | Nước: ${room.waterRate}',
                ),
                trailing: const Icon(Icons.qr_code_2),
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => MeterReadingPage(
                        services: widget.services,
                        room: room,
                      ),
                    ),
                  );
                },
              );
            },
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showCreateRoomDialog,
        child: const Icon(Icons.add),
      ),
    );
  }

  Future<void> _showCreateRoomDialog() async {
    final name = TextEditingController();
    final rent = TextEditingController();
    final elec = TextEditingController();
    final water = TextEditingController();
    final wifi = TextEditingController();
    final trash = TextEditingController();
    final parking = TextEditingController();

    final created = await showDialog<Room>(
      context: context,
      builder: (context) {
        bool loading = false;
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Tạo phòng'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: name,
                      decoration: const InputDecoration(labelText: 'Tên phòng'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: rent,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Giá phòng'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: elec,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Giá điện / số',
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: water,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Giá nước / khối',
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: wifi,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Wifi (tuỳ chọn)',
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: trash,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Rác (tuỳ chọn)',
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: parking,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Xe (tuỳ chọn)',
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: loading ? null : () => Navigator.of(context).pop(),
                  child: const Text('Huỷ'),
                ),
                FilledButton(
                  onPressed: loading
                      ? null
                      : () async {
                          setDialogState(() => loading = true);
                          try {
                            final created = await _roomApi.create(
                              boardingHouseId: widget.boardingHouse.id,
                              name: name.text.trim(),
                              rentAmount: int.tryParse(rent.text) ?? 0,
                              electricityRate: int.tryParse(elec.text) ?? 0,
                              waterRate: int.tryParse(water.text) ?? 0,
                              wifiFee: int.tryParse(wifi.text) ?? 0,
                              trashFee: int.tryParse(trash.text) ?? 0,
                              parkingFee: int.tryParse(parking.text) ?? 0,
                            );
                            if (!context.mounted) return;
                            Navigator.of(context).pop(created);
                          } on DioException catch (e) {
                            if (!context.mounted) return;
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(_extractMessage(e))),
                            );
                          } finally {
                            if (context.mounted) {
                              setDialogState(() => loading = false);
                            }
                          }
                        },
                  child: loading
                      ? const SizedBox(
                          height: 18,
                          width: 18,
                          child: CircularProgressIndicator(),
                        )
                      : const Text('Tạo'),
                ),
              ],
            );
          },
        );
      },
    );

    if (created != null) {
      setState(() {
        _future = _roomApi.list(boardingHouseId: widget.boardingHouse.id);
      });
    }
  }

  String _extractMessage(DioException e) {
    final error = e.error;
    if (error is ApiError) return error.message;
    return e.message ?? 'Có lỗi xảy ra.';
  }

  String _errorMessage(Object? error) {
    if (error is DioException) return _extractMessage(error);
    return 'Có lỗi xảy ra.';
  }
}
