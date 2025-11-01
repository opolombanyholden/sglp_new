<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test SVG</title>
</head>
<body>
    <h1>Test QR Code SVG</h1>
    @if(isset($qr_code) && !empty($qr_code->svg_content))
        {!! $qr_code->svg_content !!}
        <p>SVG affiché</p>
    @else
        <p>SVG non disponible</p>
    @endif
</body>
</html>