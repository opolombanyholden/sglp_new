<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation - {{ $documentTemplate->code }}</title>
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .preview-container {
            max-width: 21cm;
            margin: 0 auto;
            background: white;
            padding: 2cm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 29.7cm;
        }
        
        .preview-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 10px 20px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .preview-content {
            margin-top: 60px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .preview-header {
                display: none !important;
            }
            
            .preview-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
            
            .preview-content {
                margin-top: 0;
            }
        }
        
        .badge-info {
            background-color: #17a2b8;
        }
        
        .no-print {
            display: none;
        }
        
        @media screen {
            .no-print {
                display: block;
            }
        }
    </style>
</head>
<body>
    {{-- En-tête fixe avec boutons (masqué à l'impression) --}}
    <div class="preview-header no-print">
        <div>
            <strong>{{ $documentTemplate->nom }}</strong>
            <span class="badge bg-secondary ms-2">{{ $documentTemplate->code }}</span>
            @if($documentTemplate->has_qr_code)
                <span class="badge badge-info ms-1"><i class="fas fa-qrcode"></i> QR Code</span>
            @endif
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>
    </div>

    {{-- Contenu de la prévisualisation --}}
    <div class="preview-content">
        <div class="preview-container">
            @if($previewContent)
                {!! $previewContent !!}
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Template introuvable</strong>
                    <p class="mb-0">
                        Le fichier template <code>{{ $documentTemplate->template_path }}</code> n'existe pas ou contient des erreurs.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>