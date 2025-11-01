<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document['numero_document'] ?? 'Document Officiel' }}</title>
    
    <style>
        @page {
            margin: 2cm 2cm 3cm 2cm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0066cc;
        }
        
        .header img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            font-size: 16pt;
            color: #0066cc;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 9pt;
            color: #666;
            margin: 3px 0;
        }
        
        .header .ministry {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .document-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #0066cc;
            text-transform: uppercase;
            margin: 30px 0;
            letter-spacing: 1px;
        }
        
        .document-number {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        
        .content {
            text-align: justify;
            margin: 20px 0;
        }
        
        .content p {
            margin-bottom: 12px;
        }
        
        .info-box {
            border: 2px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }
        
        .info-box h3 {
            font-size: 11pt;
            color: #0066cc;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
        }
        
        .info-table td:first-child {
            background-color: #e9ecef;
            font-weight: bold;
            width: 35%;
        }
        
        .signature-block {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-block p {
            margin: 5px 0;
        }
        
        .signature-image {
            max-width: 200px;
            margin: 10px 0;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .qr-code {
            position: fixed;
            bottom: 1.5cm;
            right: 1.5cm;
            width: 3cm;
            text-align: center;
        }
        
        .qr-code svg {
            width: 3cm;
            height: 3cm;
        }
        
        .qr-code-label {
            font-size: 7pt;
            color: #666;
            margin-top: 5px;
        }
        
        .warning-box {
            margin: 20px 0;
            padding: 12px;
            border-left: 4px solid #ff9800;
            background-color: #fff3e0;
        }
        
        .warning-box strong {
            color: #ff9800;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
        
        .mt-30 {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    {{-- En-tête --}}
    @include('documents.components.header')
    
    {{-- Contenu principal --}}
    <div class="document-body">
        @yield('content')
    </div>
    
    {{-- Pied de page --}}
    @include('documents.components.footer')
    
    {{-- QR Code --}}
    @if($has_qr_code ?? true)
        @include('documents.components.qr-code')
    @endif
</body>
</html>