import 'package:app_house/api/meter_reading_api.dart';
import 'package:app_house/core/api_error.dart';
import 'package:app_house/core/app_services.dart';
import 'package:app_house/models/room.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class MeterReadingPage extends StatefulWidget {
  final AppServices services;
  final Room room;

  const MeterReadingPage({
    super.key,
    required this.services,
    required this.room,
  });

  @override
  State<MeterReadingPage> createState() => _MeterReadingPageState();
}

class _MeterReadingPageState extends State<MeterReadingPage> {
  late final MeterReadingApi _api = MeterReadingApi(
    widget.services.apiClient.dio,
  );

  final _period = TextEditingController();
  final _electricity = TextEditingController();
  final _water = TextEditingController();

  bool _loading = false;
  Map<String, dynamic>? _result;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    final m = now.month.toString().padLeft(2, '0');
    _period.text = '${now.year}-$m';
  }

  @override
  void dispose() {
    _period.dispose();
    _electricity.dispose();
    _water.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final invoice = _result == null ? null : _extractInvoice(_result!);
    final items = invoice == null
        ? const <Map<String, dynamic>>[]
        : _extractInvoiceItems(invoice);
    final invoiceTotal = invoice == null ? 0 : (_toInt(invoice['total']) ?? 0);

    return Scaffold(
      appBar: AppBar(title: Text('Nhập điện nước - ${widget.room.name}')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            TextField(
              controller: _period,
              decoration: const InputDecoration(labelText: 'Kỳ (YYYY-MM)'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _electricity,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Số điện mới'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _water,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Số nước mới'),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(
                        height: 18,
                        width: 18,
                        child: CircularProgressIndicator(),
                      )
                    : const Text('Tạo hoá đơn'),
              ),
            ),
            const SizedBox(height: 16),
            if (invoice != null) ...[
              Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'Tổng: $invoiceTotal',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ),
              const SizedBox(height: 8),
              Expanded(
                child: ListView.separated(
                  itemCount: items.length,
                  separatorBuilder: (_, _) => const Divider(height: 0),
                  itemBuilder: (context, index) {
                    final it = items[index];
                    final name = it['name'] as String? ?? '';
                    final qty = _toInt(it['quantity']) ?? 0;
                    final unit = _toInt(it['unit_price']) ?? 0;
                    final amount = _toInt(it['amount']) ?? 0;
                    return ListTile(
                      title: Text(name),
                      subtitle: Text('SL: $qty | Đơn giá: $unit'),
                      trailing: Text('$amount'),
                    );
                  },
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    setState(() {
      _loading = true;
      _result = null;
    });
    try {
      final result = await _api.create(
        roomId: widget.room.id,
        period: _period.text.trim(),
        electricityReading: int.tryParse(_electricity.text) ?? 0,
        waterReading: int.tryParse(_water.text) ?? 0,
      );

      setState(() {
        _result = result;
      });
    } on DioException catch (e) {
      _showError(_extractMessage(e));
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Map<String, dynamic>? _extractInvoice(Map<String, dynamic> payload) {
    final data = payload['data'];
    if (data is! Map) return null;
    final invoice = (data['invoice']);
    if (invoice is! Map) return null;
    return Map<String, dynamic>.from(invoice);
  }

  List<Map<String, dynamic>> _extractInvoiceItems(
    Map<String, dynamic> invoice,
  ) {
    final list = invoice['items'];
    if (list is! List) return const [];
    return list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
  }

  int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }

  String _extractMessage(DioException e) {
    final error = e.error;
    if (error is ApiError) return error.message;
    return e.message ?? 'Có lỗi xảy ra.';
  }

  void _showError(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }
}
