<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Vérification de Documents - SGLP</title>
    
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
        
        .help-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }
        
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .page-header h1 {
            color: var(--gabon-blue);
            margin-bottom: 1rem;
        }
        
        .section-title {
            color: var(--gabon-blue);
            font-weight: bold;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--gabon-yellow);
        }
        
        .step-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--gabon-green);
            transition: all 0.3s;
        }
        
        .step-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-blue));
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 1rem;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .faq-question {
            padding: 1.5rem;
            background: #f8f9fa;
            cursor: pointer;
            font-weight: 600;
            color: var(--gabon-blue);
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .faq-question:hover {
            background: #e9ecef;
            border-left-color: var(--gabon-yellow);
        }
        
        .faq-question.active {
            background: var(--gabon-blue);
            color: white;
            border-left-color: var(--gabon-yellow);
        }
        
        .faq-answer {
            padding: 1.5rem;
            display: none;
            border-top: 1px solid #dee2e6;
        }
        
        .faq-answer.show {
            display: block;
        }
        
        .icon-box {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-blue));
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .contact-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .alert-custom {
            border-left: 4px solid;
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .alert-info-custom {
            background: #d1ecf1;
            border-left-color: #0dcaf0;
            color: #055160;
        }
        
        .alert-warning-custom {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #664d03;
        }
        
        .footer-public {
            text-align: center;
            color: white;
            margin-top: 2rem;
            padding: 1rem;
        }
        
        .btn-back {
            background: white;
            color: var(--gabon-blue);
            border: 2px solid white;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: var(--gabon-yellow);
            border-color: var(--gabon-yellow);
            color: var(--gabon-blue);
            transform: translateY(-2px);
        }

        .document-example {
            border: 2px dashed #dee2e6;
            padding: 2rem;
            border-radius: 10px;
            background: #f8f9fa;
            text-align: center;
        }
        
        .code-example {
            background: #2d2d2d;
            color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <div class="container help-container">
        
        {{-- En-tête --}}
        <div class="page-header">
            <div class="icon-box mx-auto">
                <i class="fas fa-question-circle"></i>
            </div>
            <h1>Centre d'Aide</h1>
            <p class="lead text-muted mb-0">
                Guide complet pour la vérification de documents officiels
            </p>
        </div>

        {{-- Guide étape par étape --}}
        <div class="card">
            <div class="card-body p-4">
                <h3 class="section-title">
                    <i class="fas fa-book me-2"></i>
                    Comment vérifier un document ?
                </h3>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">1</div>
                        <div>
                            <h5 class="mb-2">Localisez le code de vérification</h5>
                            <p class="text-muted mb-2">
                                Sur votre document officiel, recherchez le code de vérification. 
                                Il se trouve généralement :
                            </p>
                            <ul class="mb-0">
                                <li>En bas de page du document</li>
                                <li>À côté du QR Code</li>
                                <li>Dans la section "Vérification"</li>
                            </ul>
                            <div class="document-example mt-3">
                                <p class="mb-2"><strong>Exemple de code :</strong></p>
                                <div class="code-example">
                                    ABC123XYZ789DEF456GHI
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Le code contient des lettres et des chiffres
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">2</div>
                        <div>
                            <h5 class="mb-2">Accédez au système de vérification</h5>
                            <p class="text-muted mb-2">
                                Rendez-vous sur la page de vérification :
                            </p>
                            <a href="{{ route('document.verify.index') }}" 
                               class="btn btn-primary mb-2">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Page de vérification
                            </a>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-lock me-1"></i>
                                Connexion sécurisée (HTTPS)
                            </p>
                        </div>
                    </div>
                </div>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">3</div>
                        <div>
                            <h5 class="mb-2">Saisissez le code ou scannez le QR Code</h5>
                            <p class="text-muted mb-2">
                                Vous avez deux options :
                            </p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-info-custom">
                                        <h6><i class="fas fa-keyboard me-2"></i>Option 1 : Saisie manuelle</h6>
                                        <p class="small mb-0">
                                            Tapez le code dans le formulaire en respectant 
                                            majuscules et minuscules
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-info-custom">
                                        <h6><i class="fas fa-qrcode me-2"></i>Option 2 : Scan QR Code</h6>
                                        <p class="small mb-0">
                                            Utilisez votre smartphone pour scanner 
                                            le QR Code sur le document
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-card">
                    <div class="d-flex align-items-start">
                        <div class="step-number">4</div>
                        <div>
                            <h5 class="mb-2">Consultez le résultat</h5>
                            <p class="text-muted mb-2">
                                Le système affiche instantanément :
                            </p>
                            <div class="row text-center">
                                <div class="col-md-4 mb-2">
                                    <div class="p-3 bg-success text-white rounded">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                        <p class="small mb-0"><strong>Document Valide</strong></p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="p-3 bg-warning text-dark rounded">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                        <p class="small mb-0"><strong>Document Invalidé</strong></p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="p-3 bg-danger text-white rounded">
                                        <i class="fas fa-times-circle fa-2x mb-2"></i>
                                        <p class="small mb-0"><strong>Document Introuvable</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Questions fréquentes --}}
        <div class="card">
            <div class="card-body p-4">
                <h3 class="section-title">
                    <i class="fas fa-question-circle me-2"></i>
                    Questions Fréquentes (FAQ)
                </h3>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Qu'est-ce qu'un code de vérification ?
                    </div>
                    <div class="faq-answer">
                        <p>
                            Le code de vérification est un identifiant unique attribué à chaque document 
                            officiel généré par le système SGLP. Il permet de vérifier l'authenticité 
                            du document et de consulter ses informations officielles.
                        </p>
                        <p class="mb-0">
                            Ce code est composé de lettres et de chiffres et est unique pour chaque document.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Où trouver le code de vérification sur mon document ?
                    </div>
                    <div class="faq-answer">
                        <p>Le code de vérification se trouve généralement :</p>
                        <ul>
                            <li>En bas de la première page du document</li>
                            <li>À côté ou en dessous du QR Code</li>
                            <li>Dans une section intitulée "Vérification" ou "Code de sécurité"</li>
                        </ul>
                        <p class="mb-0">
                            Si vous ne trouvez pas le code, contactez l'organisme qui vous a délivré le document.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Que signifie "Document Invalide" ?
                    </div>
                    <div class="faq-answer">
                        <p>
                            Un document invalide est un document qui a été révoqué par l'administration. 
                            Cela peut arriver pour plusieurs raisons :
                        </p>
                        <ul>
                            <li>Le document a été remplacé par une version plus récente</li>
                            <li>L'organisation concernée a cessé ses activités</li>
                            <li>Des erreurs ont été détectées dans le document</li>
                            <li>Une décision administrative a révoqué le document</li>
                        </ul>
                        <p class="mb-0">
                            <strong>Important :</strong> Un document invalide ne peut plus être utilisé 
                            comme preuve officielle. Contactez l'administration pour obtenir un nouveau document.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Que faire si mon code n'est pas reconnu ?
                    </div>
                    <div class="faq-answer">
                        <p>Si le système indique que votre code n'est pas reconnu :</p>
                        <ol>
                            <li><strong>Vérifiez la saisie :</strong> Assurez-vous d'avoir tapé le code correctement (majuscules/minuscules)</li>
                            <li><strong>Vérifiez le document :</strong> Le document doit provenir d'une source officielle</li>
                            <li><strong>Délai d'enregistrement :</strong> Les nouveaux documents peuvent prendre quelques heures avant d'être disponibles</li>
                            <li><strong>Contactez l'administration :</strong> Si le problème persiste, contactez l'organisme émetteur</li>
                        </ol>
                        <div class="alert alert-warning-custom mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention :</strong> Un document non reconnu pourrait être frauduleux. 
                            Méfiez-vous et vérifiez toujours la source.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Le système de vérification est-il sécurisé ?
                    </div>
                    <div class="faq-answer">
                        <p>
                            Oui, le système de vérification est entièrement sécurisé :
                        </p>
                        <ul>
                            <li><i class="fas fa-lock text-success me-2"></i>Connexion HTTPS chiffrée</li>
                            <li><i class="fas fa-database text-success me-2"></i>Base de données officielle de l'État</li>
                            <li><i class="fas fa-shield-alt text-success me-2"></i>Aucune donnée personnelle collectée</li>
                            <li><i class="fas fa-history text-success me-2"></i>Historique des vérifications horodaté</li>
                        </ul>
                        <p class="mb-0">
                            Chaque vérification est enregistrée (date, heure) mais aucune information 
                            personnelle identifiable n'est conservée.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Puis-je vérifier un document plusieurs fois ?
                    </div>
                    <div class="faq-answer">
                        <p>
                            Oui, vous pouvez vérifier un document autant de fois que nécessaire. 
                            Chaque vérification est enregistrée dans l'historique du document.
                        </p>
                        <p class="mb-0">
                            Le système affiche le nombre total de vérifications effectuées pour 
                            garantir la transparence.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <i class="fas fa-chevron-right me-2"></i>
                        Comment scanner un QR Code ?
                    </div>
                    <div class="faq-answer">
                        <p>Pour scanner le QR Code d'un document :</p>
                        <ol>
                            <li>Ouvrez l'appareil photo de votre smartphone</li>
                            <li>Pointez l'appareil vers le QR Code sur le document</li>
                            <li>Une notification apparaîtra avec un lien</li>
                            <li>Cliquez sur le lien pour accéder directement au résultat</li>
                        </ol>
                        <p class="mb-0">
                            <strong>Note :</strong> La plupart des smartphones modernes peuvent scanner 
                            les QR Codes directement avec l'appareil photo natif.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Contact et support --}}
        <div class="card">
            <div class="card-body p-4">
                <h3 class="section-title">
                    <i class="fas fa-headset me-2"></i>
                    Besoin d'aide supplémentaire ?
                </h3>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="contact-card">
                            <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                            <h5>Par téléphone</h5>
                            <p class="text-muted mb-2">
                                Service disponible<br>
                                Lun-Ven : 8h - 17h
                            </p>
                            <a href="tel:+24111000000" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>
                                +241 11 00 00 00
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="contact-card">
                            <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                            <h5>Par email</h5>
                            <p class="text-muted mb-2">
                                Réponse sous 48h<br>
                                Jours ouvrables
                            </p>
                            <a href="mailto:support@sglp.ga" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>
                                support@sglp.ga
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="contact-card">
                            <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                            <h5>En personne</h5>
                            <p class="text-muted mb-2">
                                Ministère de l'Intérieur<br>
                                Libreville, Gabon
                            </p>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-map me-2"></i>
                                Voir la carte
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info-custom mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note importante :</strong>
                    Pour toute question concernant un document spécifique, veuillez contacter 
                    directement l'organisme qui vous a délivré le document.
                </div>
            </div>
        </div>

        {{-- Retour à la vérification --}}
        <div class="text-center mb-4">
            <a href="{{ route('document.verify.index') }}" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>
                Retour à la vérification
            </a>
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
                    Tous droits réservés © {{ date('Y') }}
                </small>
            </p>
        </div>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle FAQ
        function toggleFaq(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('i');
            
            // Fermer toutes les autres FAQ
            document.querySelectorAll('.faq-answer').forEach(faq => {
                if (faq !== answer) {
                    faq.classList.remove('show');
                }
            });
            
            document.querySelectorAll('.faq-question').forEach(question => {
                if (question !== element) {
                    question.classList.remove('active');
                    question.querySelector('i').classList.remove('fa-chevron-down');
                    question.querySelector('i').classList.add('fa-chevron-right');
                }
            });
            
            // Toggle la FAQ actuelle
            answer.classList.toggle('show');
            element.classList.toggle('active');
            
            if (answer.classList.contains('show')) {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>

</body>
</html>