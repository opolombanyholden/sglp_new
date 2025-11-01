<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test PNG</title>
</head>
<body>
    <h1>Test QR Code PNG</h1>
    @if(isset($qr_code) && !empty($qr_code->png_base64))
        <img src="data:image/png;base64,{{ $qr_code->png_base64 }}" width="100" height="100">
        <p>PNG affiché</p>
    @else
        <p>PNG non disponible</p>
    @endif
</body>
</html>