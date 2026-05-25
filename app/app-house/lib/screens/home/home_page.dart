import 'package:app_house/api/auth_api.dart';
import 'package:app_house/api/boarding_house_api.dart';
import 'package:app_house/core/api_error.dart';
import 'package:app_house/core/app_services.dart';
import 'package:app_house/models/boarding_house.dart';
import 'package:app_house/screens/auth/login_register_page.dart';
import 'package:app_house/screens/rooms/rooms_page.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class HomePage extends StatefulWidget {
  final AppServices services;

  const HomePage({super.key, required this.services});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  late final BoardingHouseApi _boardingHouseApi = BoardingHouseApi(
    widget.services.apiClient.dio,
  );
  late final AuthApi _authApi = AuthApi(widget.services.apiClient.dio);

  late Future<List<BoardingHouse>> _future = _boardingHouseApi.list();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Nhà trọ'),
        actions: [
          IconButton(onPressed: _handleLogout, icon: const Icon(Icons.logout)),
        ],
      ),
      body: FutureBuilder<List<BoardingHouse>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            final message = _errorMessage(snapshot.error);
            return Center(child: Text(message));
          }

          final items = snapshot.data ?? const [];
          if (items.isEmpty) {
            return const Center(child: Text('Chưa có dãy trọ. Bấm + để tạo.'));
          }

          return ListView.separated(
            itemCount: items.length,
            separatorBuilder: (_, _) => const Divider(height: 0),
            itemBuilder: (context, index) {
              final item = items[index];
              return ListTile(
                title: Text(item.name),
                subtitle: item.address == null || item.address!.isEmpty
                    ? null
                    : Text(item.address!),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => RoomsPage(
                        services: widget.services,
                        boardingHouse: item,
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
        onPressed: _showCreateBoardingHouseDialog,
        child: const Icon(Icons.add),
      ),
    );
  }

  Future<void> _showCreateBoardingHouseDialog() async {
    final nameController = TextEditingController();
    final addressController = TextEditingController();

    final created = await showDialog<BoardingHouse>(
      context: context,
      builder: (context) {
        bool loading = false;
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Tạo dãy trọ'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(
                    controller: nameController,
                    decoration: const InputDecoration(labelText: 'Tên'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: addressController,
                    decoration: const InputDecoration(
                      labelText: 'Địa chỉ (tuỳ chọn)',
                    ),
                  ),
                ],
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
                            final result = await _boardingHouseApi.create(
                              name: nameController.text.trim(),
                              address: addressController.text.trim().isEmpty
                                  ? null
                                  : addressController.text.trim(),
                            );
                            if (!context.mounted) return;
                            Navigator.of(context).pop(result);
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
        _future = _boardingHouseApi.list();
      });
    }
  }

  Future<void> _handleLogout() async {
    try {
      await _authApi.logout();
    } catch (_) {}

    await widget.services.tokenStore.deleteToken();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (_) => LoginRegisterPage(services: widget.services),
      ),
    );
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
