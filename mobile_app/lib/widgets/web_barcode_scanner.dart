// Conditional import: loads the web implementation only on web platform.
export 'web_barcode_scanner_stub.dart'
    if (dart.library.html) 'web_barcode_scanner_web.dart';
