import 'dart:async';
import 'dart:convert';
// ignore: avoid_web_libraries_in_flutter
import 'dart:html' as html;
// ignore: avoid_web_libraries_in_flutter
import 'dart:js' as js;

import 'package:flutter/material.dart';

/// Web barcode scanner using native BarcodeDetector API (ML Kit on Chrome Android)
/// with direct camera control for optimal quality.
class WebBarcodeScanner extends StatefulWidget {
  final void Function(String barcode) onDetect;
  final VoidCallback? onSearch;

  const WebBarcodeScanner({super.key, required this.onDetect, this.onSearch});

  /// Hide scanner overlay and pause camera to show Flutter UI underneath
  static void hideOverlay() {
    // Pause camera + hide all scanner elements via JS
    html.document.querySelectorAll('[id^="wm-qr-reader-"]').forEach((el) {
      final id = (el as html.Element).id;
      try { js.context.callMethod('pauseBarcodeScanner', [id]); } catch (_) {}
      el.style.display = 'none';
    });
    // Also hide Dart-created controls (close btn, torch btn, zoom slider)
    _hideFixedControls(true);
  }

  /// Show scanner overlay and resume camera
  static void showOverlay() {
    html.document.querySelectorAll('[id^="wm-qr-reader-"]').forEach((el) {
      final id = (el as html.Element).id;
      (el as html.Element).style.display = '';
      try { js.context.callMethod('resumeBarcodeScanner', [id]); } catch (_) {}
    });
    _hideFixedControls(false);
  }

  static void _hideFixedControls(bool hide) {
    // Hide/show Dart-created fixed elements (close btn, torch btn, zoom container)
    final display = hide ? 'none' : '';
    for (final selector in ['button[style*="z-index:99999"]', 'div[style*="z-index:99999"]']) {
      html.document.querySelectorAll(selector).forEach((el) {
        (el as html.Element).style.display = display;
      });
    }
  }

  @override
  State<WebBarcodeScanner> createState() => _WebBarcodeScannerState();
}

class _WebBarcodeScannerState extends State<WebBarcodeScanner> {
  static int _counter = 0;
  late final String _divId;
  html.DivElement? _scannerDiv;
  html.DivElement? _loadingOverlay;
  html.ButtonElement? _closeBtn;
  html.ButtonElement? _torchBtn;
  html.DivElement? _zoomContainer;
  bool _started = false;
  bool _torchOn = false;
  Timer? _timeoutTimer;

  @override
  void initState() {
    super.initState();
    _divId = 'wm-qr-reader-${++_counter}';
    _createScannerDiv();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      Timer(const Duration(milliseconds: 200), _startScanner);
    });
  }

  void _createScannerDiv() {
    _scannerDiv = html.DivElement()
      ..id = _divId
      ..style.position = 'fixed'
      ..style.top = '0'
      ..style.left = '0'
      ..style.width = '100vw'
      ..style.height = '100vh'
      ..style.zIndex = '10000'
      ..style.backgroundColor = '#000';

    // Loading overlay
    _loadingOverlay = html.DivElement()
      ..style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;'
          'z-index:10002;display:flex;flex-direction:column;'
          'align-items:center;justify-content:center;background:#000;';
    _loadingOverlay!.innerHtml =
        '<style>@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}</style>'
        '<div style="color:#fff;font-size:16px;text-align:center;">'
        '<div style="border:3px solid #555;border-top:3px solid #fff;'
        'border-radius:50%;width:40px;height:40px;margin:0 auto 16px;'
        'animation:spin 1s linear infinite;"></div>'
        'Activation de la cam\u00e9ra...<br>'
        '<small style="color:#999;">Autorisez l\'acc\u00e8s si demand\u00e9</small>'
        '</div>';
    _scannerDiv!.append(_loadingOverlay!);

    html.document.body!.append(_scannerDiv!);

    // Close button (on body, z-index max)
    _closeBtn = html.ButtonElement()
      ..text = '\u2190 Retour'
      ..style.cssText = 'position:fixed;top:12px;left:12px;z-index:99999;'
          'background:rgba(0,0,0,0.8);color:#fff;border:2px solid #fff;'
          'border-radius:10px;padding:12px 24px;font-size:18px;font-weight:bold;'
          'cursor:pointer;-webkit-tap-highlight-color:transparent;';
    _closeBtn!.onClick.listen((_) => _closeScanner());
    html.document.body!.append(_closeBtn!);
  }

  void _startScanner() {
    if (!mounted || _started) return;

    _timeoutTimer = Timer(const Duration(seconds: 15), () {
      if (mounted && _loadingOverlay?.style.display != 'none') {
        _showError('D\u00e9lai d\u00e9pass\u00e9. V\u00e9rifiez les permissions cam\u00e9ra.');
      }
    });

    try {
      js.context.callMethod('startBarcodeScanner', [
        _divId,
        // onSuccess
        js.allowInterop((String barcode) {
          if (mounted) widget.onDetect(barcode);
        }),
        // onError
        js.allowInterop((String error) {
          _showError(error);
        }),
        // onStart
        js.allowInterop(() {
          _timeoutTimer?.cancel();
          _loadingOverlay?.style.display = 'none';
          _addControls();
        }),
        // onClose
        js.allowInterop(() {
          if (mounted) _closeScanner();
        }),
        // onSearch
        widget.onSearch != null
            ? js.allowInterop(() {
                if (mounted) widget.onSearch!();
              })
            : null,
      ]);
      _started = true;
    } catch (e) {
      _showError(e.toString());
    }
  }

  void _showError(String error) {
    _timeoutTimer?.cancel();
    if (_loadingOverlay != null) {
      _loadingOverlay!.innerHtml =
          '<div style="color:#fff;text-align:center;padding:24px;">'
          '<div style="font-size:48px;margin-bottom:16px;">\u274C</div>'
          '<div style="font-size:18px;font-weight:bold;margin-bottom:8px;">'
          'Impossible d\'activer la cam\u00e9ra</div>'
          '<div style="color:#999;font-size:13px;margin-bottom:12px;">'
          '${_escapeHtml(error)}</div>'
          '<div style="color:#bbb;font-size:13px;">'
          'V\u00e9rifiez les permissions et HTTPS</div></div>';
      _loadingOverlay!.style.display = 'flex';
    }
  }

  String _escapeHtml(String s) =>
      s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');

  void _addControls() {
    if (_scannerDiv == null) return;

    // Torch button (top-right) - torch is auto-enabled at start
    final hasTorch = js.context.callMethod('getBarcodeScannerHasTorch', [_divId]) == 'true';
    if (hasTorch) {
      _torchOn = false; // Torch OFF by default
      _torchBtn = html.ButtonElement()
        ..text = '\uD83D\uDD26'  // 🔦
        ..style.cssText = 'position:fixed;top:12px;right:12px;z-index:99999;'
            'background:rgba(0,0,0,0.8);color:#fff;border:2px solid #fff;'
            'border-radius:10px;padding:12px 16px;font-size:22px;'
            'cursor:pointer;-webkit-tap-highlight-color:transparent;';
      _torchBtn!.onClick.listen((_) {
        js.context.callMethod('toggleBarcodeScannerTorch', [_divId]);
        _torchOn = !_torchOn;
        _torchBtn!.style.background = _torchOn ? 'rgba(255,200,0,0.8)' : 'rgba(0,0,0,0.8)';
      });
      html.document.body!.append(_torchBtn!);
    }

    // Zoom slider
    try {
      final jsonStr = js.context.callMethod('getBarcodeScannerZoomInfo', [_divId]) as String;
      if (jsonStr.isNotEmpty && jsonStr != '{}') {
        final info = json.decode(jsonStr) as Map<String, dynamic>;
        final min = (info['min'] as num).toDouble();
        final max = (info['max'] as num).toDouble();
        final current = (info['current'] as num).toDouble();
        if (max > min) _createZoomSlider(min, max, current);
      }
    } catch (_) {}
  }

  void _createZoomSlider(double min, double max, double current) {
    _zoomContainer = html.DivElement()
      ..style.cssText = 'position:fixed;bottom:80px;left:16px;right:16px;z-index:99999;'
          'display:flex;align-items:center;'
          'background:rgba(0,0,0,0.7);border-radius:24px;padding:10px 16px;';

    final labelMinus = html.SpanElement()
      ..text = '\u2796'
      ..style.cssText = 'color:#fff;font-size:18px;padding:0 8px;';

    final slider = html.InputElement(type: 'range')
      ..min = min.toString()
      ..max = max.toString()
      ..value = current.toString()
      ..step = '0.1'
      ..style.cssText = 'flex:1;accent-color:#fff;height:32px;';

    final labelPlus = html.SpanElement()
      ..text = '\u2795'
      ..style.cssText = 'color:#fff;font-size:18px;padding:0 8px;';

    final valueLabel = html.SpanElement()
      ..text = '${current.toStringAsFixed(1)}x'
      ..style.cssText = 'color:#fff;font-size:14px;font-weight:bold;'
          'min-width:40px;text-align:right;';

    slider.onInput.listen((_) {
      final val = double.tryParse(slider.value ?? '') ?? current;
      valueLabel.text = '${val.toStringAsFixed(1)}x';
      try {
        js.context.callMethod('setBarcodeScannerZoom', [_divId, val]);
      } catch (_) {}
    });

    _zoomContainer!.children.addAll([labelMinus, slider, labelPlus, valueLabel]);
    html.document.body!.append(_zoomContainer!);
  }

  void _closeScanner() {
    _stopScanner();
    _removeAll();
    if (mounted) Navigator.of(context).pop();
  }

  void _stopScanner() {
    _timeoutTimer?.cancel();
    if (!_started) return;
    try {
      js.context.callMethod('stopBarcodeScanner', [_divId]);
    } catch (_) {}
    _started = false;
  }

  void _removeAll() {
    _closeBtn?.remove();
    _closeBtn = null;
    _torchBtn?.remove();
    _torchBtn = null;
    _zoomContainer?.remove();
    _zoomContainer = null;
    _scannerDiv?.remove();
    _scannerDiv = null;
    _loadingOverlay = null;
  }

  @override
  void dispose() {
    _stopScanner();
    _removeAll();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return const SizedBox.shrink();
  }
}
