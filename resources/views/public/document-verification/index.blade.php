<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Documents Officiels - SGLP</title>
    
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
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: white;
            border-bottom: 3px solid var(--gabon-yellow);
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo-placeholder {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-blue));
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
        }
        
        .input-token {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .input-token:focus {
            border-color: var(--gabon-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
        }
        
        .btn-verify {
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-blue));
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 158, 63, 0.4);
        }
        
        .help-text {
            background: #f8f9fa;
            border-left: 4px solid var(--gabon-yellow);
            padding: 1rem;
            border-radius: 5px;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-blue));
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .footer-public {
            text-align: center;
            color: white;
            margin-top: 2rem;
            padding: 1rem;
        }
        
        .qr-scanner-btn {
            border: 2px dashed var(--gabon-blue);
            background: #f8f9fa;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .qr-scanner-btn:hover {
            background: white;
            border-color: var(--gabon-green);
        }
    </style>
</head>
<body>

    <div class="container verification-container py-5">
        
        {{-- Card principale --}}
        <div class="card">
            <div class="card-header">
                <div class="logo-container">
                    <div class="logo-placeholder">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="mt-3 mb-1">République Gabonaise</h3>
                    <p class="text-muted mb-0">Vérification de Documents Officiels</p>
                </div>
            </div>
            
            <div class="card-body p-4">
                
                {{-- Alertes --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erreur :</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erreur :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                {{-- Formulaire de vérification --}}
                <form action="{{ route('document.verify.check') }}" method="POST" id="verificationForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="token" class="form-label fw-bold">
                            <i class="fas fa-key text-primary"></i> Code de vérification
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg input-token" 
                               id="token" 
                               name="token" 
                               placeholder="Entrez le code du document"
                               value="{{ old('token') }}"
                               required
                               autocomplete="off">
                        <small class="form-text text-muted">
                            Le code se trouve sur votre document officiel
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-verify w-100">
                        <i class="fas fa-search me-2"></i> Vérifier le document
                    </button>
                </form>

                {{-- Séparateur --}}
                <div class="text-center my-4">
                    <span class="text-muted">OU</span>
                </div>

                {{-- Scanner QR Code --}}
                <div class="qr-scanner-btn" onclick="openQRScanner()">
                    <i class="fas fa-qrcode fa-3x text-primary mb-2"></i>
                    <h5>Scanner un QR Code</h5>
                    <p class="text-muted mb-0">Utilisez l'appareil photo pour scanner</p>
                </div>

            </div>
        </div>

        {{-- Aide et informations --}}
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-info-circle text-primary"></i> Comment vérifier un document ?
                </h5>
                
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-1"></i>
                        </div>
                        <h6>Localisez le code</h6>
                        <small class="text-muted">
                            Trouvez le code de vérification sur votre document
                        </small>
                    </div>
                    
                    <div class="col-md-4 text-center mb-3">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-2"></i>
                        </div>
                        <h6>Saisissez le code</h6>
                        <small class="text-muted">
                            Entrez le code dans le formulaire ci-dessus
                        </small>
                    </div>
                    
                    <div class="col-md-4 text-center mb-3">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-3"></i>
                        </div>
                        <h6>Vérifiez</h6>
                        <small class="text-muted">
                            Consultez instantanément l'authenticité du document
                        </small>
                    </div>
                </div>

                <div class="help-text mt-3">
                    <strong><i class="fas fa-lightbulb text-warning"></i> Astuce :</strong>
                    Si votre document comporte un QR Code, vous pouvez le scanner directement 
                    avec votre smartphone pour une vérification instantanée.
                </div>
            </div>
        </div>

        {{-- Sécurité et confidentialité --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-lock fa-2x text-success mb-2"></i>
                        <h6>Sécurisé</h6>
                        <small class="text-muted">Connexion chiffrée SSL</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                        <h6>Authentique</h6>
                        <small class="text-muted">Base de données officielle</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                        <h6>Instantané</h6>
                        <small class="text-muted">Résultat immédiat</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer-public">
            <p class="mb-1">
                <strong>Système de Gestion des Libertés Publiques (SGLP)</strong>
            </p>
            <p class="mb-0">
                <small>
                    République Gabonaise - Ministère de l'Intérieur
                    <br>
                    <a href="{{ route('document.verify.help') }}" class="text-white text-decoration-underline">
                        Besoin d'aide ?
                    </a>
                </small>
            </p>
        </div>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validation du formulaire
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            const token = document.getElementById('token').value.trim();
            
            if (token.length < 10) {
                e.preventDefault();
                alert('Le code de vérification semble trop court. Veuillez vérifier.');
                document.getElementById('token').focus();
                return false;
            }
        });

        // Scanner QR Code
        function openQRScanner() {
            alert('Fonctionnalité de scan QR Code à venir.\n\nPour le moment, veuillez saisir manuellement le code de vérification.');
            // TODO: Implémenter le scanner QR Code avec une bibliothèque JS
        }

        // Auto-focus sur le champ token
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('token').focus();
        });

        // Formatage du token pendant la saisie (optionnel)
        document.getElementById('token').addEventListener('input', function(e) {
            // Supprimer les espaces
            this.value = this.value.replace(/\s+/g, '');
        });
    </script>

</body>
</html>