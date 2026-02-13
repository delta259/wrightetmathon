import 'package:flutter/material.dart';

/// Stub implementation for non-web platforms.
class WebBarcodeScanner extends StatelessWidget {
  final void Function(String barcode) onDetect;
  final VoidCallback? onSearch;

  const WebBarcodeScanner({super.key, required this.onDetect, this.onSearch});

  static void hideOverlay() {}
  static void showOverlay() {}

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('Scanner web non disponible sur cette plateforme'),
    );
  }
}
