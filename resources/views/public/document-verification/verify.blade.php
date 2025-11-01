<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat de Vérification - SGLP</title>
    
    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --gabon-green: #009e3f;
            --gabon-yellow: #ffcd00;
            --gabon-blue: #003f7f;
        }
        
        body {
            background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
            min-height: 100vh;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .status-header {
            padding: 3rem 2rem;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .status-header.valid {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .status-header.invalid {
            background: linear-gradient(135deg, #ffc107, #ff9800);
        }
        
        .status-header.not-found {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        
        .status-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .status-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid var(--gabon-yellow);
        }
        
        .info-item-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .info-item-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
        }
        
        .document-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .btn-action {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .danger-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .footer-public {
            text-align: center;
            color: white;
            margin-top: 2rem;
            padding: 1rem;
        }
        
        .verification-details {
            font-size: 0.9rem;
            color: #6c757d;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 1.5rem;
        }
        
        .qr-code-display {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .print-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            font-size: 1.5rem;
            color: var(--gabon-blue);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .print-button:hover {
            transform: scale(1.1);
            background: var(--gabon-yellow);
        }

        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>

    <div class="container verification-container">
        
        @if($document)
            {{-- DOCUMENT VALIDE --}}
            @if($document->is_valid)
                <div class="card">
                    <div class="status-header valid">
                        <div class="status-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1 class="status-title">Document Authentique</h1>
                        <p class="mb-0">Ce document est officiellement reconnu par l'État Gabonais</p>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        {{-- Informations principales --}}
                        <div class="text-center mb-4">
                            <span class="document-badge">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                {{ $document->numero_document }}
                            </span>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-item-label">
                                    <i class="fas fa-building"></i> Organisation
                                </div>
                                <div class="info-item-value">
                                    {{ $document->organisation->nom }}
                                </div>
                                @if($document->organisation->sigle)
                                    <small class="text-muted">{{ $document->organisation->sigle }}</small>
                                @endif
                            </div>

                            <div class="info-item">
                                <div class="info-item-label">
                                    <i class="fas fa-file"></i> Type de document
                                </div>
                                <div class="info-item-value">
                                    {{ $document->documentTemplate->type_document_label }}
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item-label">
                                    <i class="fas fa-calendar"></i> Date de génération
                                </div>
                                <div class="info-item-value">
                                    {{ $document->generated_at->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">
                                    {{ $document->generated_at->diffForHumans() }}
                                </small>
                            </div>

                            <div class="info-item">
                                <div class="info-item-label">
                                    <i class="fas fa-map-marker-alt"></i> Lieu
                                </div>
                                <div class="info-item-value">
                                    {{ $document->organisation->siege_social ?? 'Non spécifié' }}
                                </div>
                            </div>
                        </div>

                        {{-- QR Code si disponible --}}
                        @if($document->qr_code_path && file_exists(public_path($document->qr_code_path)))
                            <div class="qr-code-display mt-4">
                                <h6 class="mb-3">QR Code du document</h6>
                                <img src="{{ asset($document->qr_code_path) }}" 
                                     alt="QR Code" 
                                     style="max-width: 200px;">
                                <p class="text-muted small mt-2 mb-0">
                                    Scannez ce code pour vérifier à nouveau
                                </p>
                            </div>
                        @endif

                        {{-- Détails de vérification --}}
                        <div class="verification-details">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Détails de vérification</strong>
                            <br>
                            <small>
                                Vérification effectuée le {{ now()->format('d/m/Y à H:i:s') }}
                                <br>
                                Token : <code>{{ $document->verification_token }}</code>
                                <br>
                                Nombre de vérifications : {{ $document->verifications->count() + 1 }}
                            </small>
                        </div>

                        {{-- Actions --}}
                        <div class="text-center mt-4 no-print">
                            <a href="{{ route('admin.documents.download', $document) }}" 
                               class="btn btn-primary btn-action me-2"
                               target="_blank">
                                <i class="fas fa-download me-2"></i>Télécharger le PDF
                            </a>
                            <a href="{{ route('document.verify.index') }}" 
                               class="btn btn-outline-secondary btn-action">
                                <i class="fas fa-arrow-left me-2"></i>Nouvelle vérification
                            </a>
                        </div>

                    </div>
                </div>

            @else
                {{-- DOCUMENT INVALIDE --}}
                <div class="card">
                    <div class="status-header invalid">
                        <div class="status-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h1 class="status-title">Document Non Valide</h1>
                        <p class="mb-0">Ce document a été révoqué ou invalidé</p>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        <div class="text-center mb-4">
                            <span class="document-badge">
                                <i class="fas fa-ban text-danger me-2"></i>
                                {{ $document->numero_document }}
                            </span>
                        </div>

                        <div class="warning-box">
                            <h5 class="mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Attention
                            </h5>
                            <p class="mb-2">
                                Ce document existe dans notre base de données mais a été invalidé.
                            </p>
                            @if($document->invalidation_reason)
                                <p class="mb-0">
                                    <strong>Motif :</strong> {{ $document->invalidation_reason }}
                                </p>
                            @endif
                            @if($document->invalidated_at)
                                <p class="mb-0">
                                    <strong>Date d'invalidation :</strong> 
                                    {{ $document->invalidated_at->format('d/m/Y à H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="info-grid mt-4">
                            <div class="info-item">
                                <div class="info-item-label">
                                    <i class="fas fa-building"></i> Organisation
                                </div>
                                <div class="info-item-value">
                                    {{ $document->organisation->nom }}
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item-label">
                                    <i class="fas fa-calendar"></i> Généré le
                                </div>
                                <div class="info-item-value">
                                    {{ $document->generated_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Que faire ?</strong>
                            <br>
                            Contactez l'administration émettrice pour obtenir un nouveau document valide 
                            ou pour connaître les raisons de l'invalidation.
                        </div>

                        <div class="text-center mt-4 no-print">
                            <a href="{{ route('document.verify.index') }}" 
                               class="btn btn-outline-secondary btn-action">
                                <i class="fas fa-arrow-left me-2"></i>Nouvelle vérification
                            </a>
                        </div>

                    </div>
                </div>
            @endif

        @else
            {{-- DOCUMENT NON TROUVÉ --}}
            <div class="card">
                <div class="status-header not-found">
                    <div class="status-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h1 class="status-title">Document Introuvable</h1>
                    <p class="mb-0">Aucun document ne correspond à ce code</p>
                </div>
                
                <div class="card-body p-4">
                    
                    <div class="danger-box">
                        <h5 class="mb-2">
                            <i class="fas fa-shield-alt me-2"></i>
                            Document non reconnu
                        </h5>
                        <p class="mb-2">
                            Le code de vérification que vous avez saisi ne correspond à aucun document 
                            officiel dans notre base de données.
                        </p>
                        <p class="mb-0">
                            <strong>Code saisi :</strong> <code>{{ request('token') }}</code>
                        </p>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Raisons possibles
                        </h6>
                        <ul class="mb-0">
                            <li>Le code a été mal saisi ou est incomplet</li>
                            <li>Le document n'a pas encore été enregistré dans le système</li>
                            <li>Le code ne correspond pas à un document officiel</li>
                            <li>Le document pourrait être frauduleux</li>
                        </ul>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Conseils :</strong>
                        <ul class="mb-0">
                            <li>Vérifiez que vous avez saisi correctement le code</li>
                            <li>Assurez-vous que le document provient d'une source officielle</li>
                            <li>Contactez l'administration émettrice pour confirmation</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4 no-print">
                        <a href="{{ route('document.verify.index') }}" 
                           class="btn btn-primary btn-action">
                            <i class="fas fa-redo me-2"></i>Réessayer
                        </a>
                        <a href="{{ route('document.verify.help') }}" 
                           class="btn btn-outline-secondary btn-action ms-2">
                            <i class="fas fa-question-circle me-2"></i>Besoin d'aide ?
                        </a>
                    </div>

                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer-public no-print">
            <p class="mb-1">
                <strong>Système de Gestion des Libertés Publiques (SGLP)</strong>
            </p>
            <p class="mb-0">
                <small>
                    République Gabonaise - Ministère de l'Intérieur
                    <br>
                    Pour toute question, contactez les services compétents
                </small>
            </p>
        </div>

    </div>

    {{-- Bouton d'impression --}}
    @if($document && $document->is_valid)
        <button class="print-button no-print" onclick="window.print()" title="Imprimer">
            <i class="fas fa-print"></i>
        </button>
    @endif

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enregistrer la vérification
        @if($document)
            console.log('Document vérifié : {{ $document->numero_document }}');
            console.log('Statut : {{ $document->is_valid ? "Valide" : "Invalide" }}');
        @endif

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>

</body>
</html>