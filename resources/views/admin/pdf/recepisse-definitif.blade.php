<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récépissé Définitif - {{ $nom_organisation }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        /* ===== STYLES QR CODE HARMONISÉS AVEC L'ACCUSÉ ET LE RÉCÉPISSÉ PROVISOIRE ===== */
        .qr-section {
            margin-top: 70px;
            margin-left: -150px;
            padding-top: 20px;
            width: 120px;
            position: relative;
        }

        .qr-content {
            display: table;
            width: 100%;
        }

        .qr-left {
            display: table-cell;
            width: 150px;
            vertical-align: top;
            padding: 10px;
        }

        .qr-right {
            display: table-cell;
            vertical-align: top;
            text-align: center;
            padding: 10px;
        }

        .qr-box {
            text-align: center;
            width: 100px;
        }

        .qr-image {
            display: block;
            margin: auto;
            color: #000000;
        }

        .qr-text {
            font-size: 8pt;
            color: #000000;
            font-weight: bold;
            text-transform: uppercase;
            margin: 5px 0;
        }

        .qr-code-id {
            font-size: 7pt;
            color: #666;
            font-family: monospace;
            margin: 5px 0;
        }

        .qr-url {
            font-size: 6pt;
            color: #666;
            word-break: break-all;
            margin-top: 5px;
        }

        .footer-content {
            font-size: 10pt;
            line-height: 1.3;
        }

        .footer-content strong {
            color: #003f7f;
        }

        .footer-content em {
            font-style: italic;
            color: #666;
            font-size: 9pt;
        }
        
        /* En-tête harmonisé avec les autres documents */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        
        .header-left {
            color: #000000;
            font-weight: bold;
            font-size: 14px;
            vertical-align: top;
            width: 400px;
            text-align: left;
        }
        
        .header-right {
            color: #003f7f;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            vertical-align: top;
        }
        
        .document-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            text-transform: uppercase;
        }
        
        .content {
            text-align: justify;
            margin: 20px 0;
            line-height: 1.5;
        }
        
        .content p {
            margin-bottom: 12px;
        }
        
        .organization-details {
            margin: 20px 0;
        }
        
        .detail-line {
            margin-bottom: 8px;
        }
        
        .dirigeants-section {
            margin: 15px 0;
        }
        
        .dirigeant-line {
            margin-bottom: 5px;
        }
        
        .pieces-section {
            margin: 20px 0;
        }
        
        .pieces-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        
        .pieces-list {
            margin-left: 20px;
            text-align: justify;
        }
        
        .prescriptions-section {
            margin: 25px 0;
        }
        
        .prescription-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        
        .prescription-content {
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 15px;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-location {
            margin-bottom: 20px;
        }
        
        .minister-title {
            font-weight: bold;
            margin: 20px 0;
        }
        
        .minister-name {
            font-weight: bold;
            margin-top: 60px;
        }
        
        .ampliations-section {
            margin-top: 30px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .ampliations-list {
            margin-left: 20px;
            font-weight: normal;
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
        }
        
        .ampliation-item {
            display: flex;
            justify-content: space-between;
            width: 120px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .highlight {
            font-weight: bold;
        }
        
        ul {
            margin: 0;
            padding-left: 20px;
        }
        
        li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    
    <!-- En-tête harmonisé avec l'accusé et le récépissé provisoire -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div style="font-size:18px; font-weight: bold; margin-top:150px;">
                    N° {{ $numero_administratif ?? 'XXXX/MISD/SG/DGELP/DPPALC' }}
                </div>
            </td>
            <td width="70"></td>
            <td class="header-right"></td>
        </tr>
    </table>

    <!-- Titre du document -->
    <div class="document-title">
        RÉCÉPISSÉ DÉFINITIF DE LÉGALISATION
        <div style="width:70%; margin-left:15%; height:4px; background-color:#F00;"></div>
    </div>

    <!-- Contenu principal -->
    <div class="content">
        <p><strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong></p>
        
        <p>
            Agissant conformément à ses attributions en matière de Libertés Publiques, délivre à {{ $civilite ?? 'Monsieur' }} 
            <strong>{{ $nom_prenom ?? 'NOM PRÉNOM' }}</strong>, de nationalité {{ $nationalite ?? 'gabonaise' }}, 
            domicilié à {{ $domicile ?? 'ADRESSE' }}, 
            
            {{-- ✅ CORRECTION : Affichage conditionnel du téléphone (IDENTIQUE AUX AUTRES DOCUMENTS) --}}
            @if(isset($telephone) && $telephone !== 'Non renseigné')
                Téléphone : <span class="">{{ $telephone }}</span>,
            @else
                {{-- Essayer le téléphone de l'organisation comme fallback --}}
                @if(isset($org_telephone) && $org_telephone !== 'Non renseigné')
                    Téléphone : <span class="">{{ $org_telephone }}</span>,
                @endif
            @endif
            
            un récépissé définitif de déclaration de {{ $type_organisation_label ?? 'Parti politique' }}, conformément <strong>{{ $loi_reference ?? 'à la loi n°016/2025 du 27 juin 2025 relative aux partis politiques en République Gabonaise' }}</strong>.
        </p>
    </div>

    <!-- Détails de l'organisation avec variables harmonisées -->
    <div class="organization-details">
        <div class="detail-line">
            <span class="highlight"><u>Dénomination :</u></span> 
            <span class="highlight">« {{ $nom_organisation }} »</span>{{ $sigle_organisation ? ', en abrégé ' : '' }}<span class="highlight">{{ $sigle_organisation ?? '' }}</span>
        </div>
        
        <div class="detail-line">
            <span class="highlight"><u>Siège Social</u> :</span> {{ $adresse_siege ?? $org_adresse ?? 'Libreville, GABON' }} ; 
            @if(isset($boite_postale) && $boite_postale)
                <strong>BP : {{ $boite_postale }} ; </strong>
            @endif
            <strong>Tél : {{ $org_telephone ?? 'Non renseigné' }}.</strong>
        </div>
        
        @if(isset($objet_organisation) && $objet_organisation !== 'Non spécifié')
        <div class="detail-line">
            <span class="highlight"><u>Objet :</u></span> {{ $objet_organisation }}
        </div>
        @endif
    </div>

    <!-- Section Directoire -->
    <div class="dirigeants-section">
        <div class="detail-line">
            <span class="highlight"><u>Directoire :</u></span>
        </div>
        @if(isset($dirigeants) && count($dirigeants) > 0)
            @foreach($dirigeants as $dirigeant)
                <div class="dirigeant-line">
                    • <span class="highlight"><u>{{ $dirigeant['poste'] }} :</u></span> {{ $dirigeant['nom_prenom'] }} ;
                </div>
            @endforeach
        @else
            {{-- Fallback avec les informations du mandataire harmonisées --}}
            <div class="dirigeant-line">
                • <span class="highlight"><u>Président-Fondateur :</u></span> {{ $nom_prenom ?? 'Non désigné' }} ;
            </div>
            <div class="dirigeant-line">
                • <span class="highlight"><u>Secrétaire Général :</u></span> {{ $secretaire_general ?? 'Non désigné' }} ;
            </div>
            <div class="dirigeant-line">
                • <span class="highlight"><u>Trésorier :</u></span> {{ $tresorier ?? 'Non désigné' }}.
            </div>
        @endif
    </div>

    <!-- Pièces annexées -->
    <div class="pieces-section">
        <div class="pieces-title">Pièces annexées à la déclaration et autres prescriptions :</div>
        
        <div style="margin-bottom: 15px;">
            <span class="highlight"><u>1. Pièces annexées :</u></span>
            <div class="pieces-list">
                @if(isset($pieces_annexees) && count($pieces_annexees) > 0)
                    @foreach($pieces_annexees as $piece)
                        {{ $piece }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                @else
                    Statuts du parti, règlement intérieur, procès-verbal de la réunion constitutive du parti, liste des membres du directoire, 
                    copies certifiées conformes des cartes nationales d'identité ou passeports des membres fondateurs et dirigeants du parti politique, 
                    ainsi que leurs extraits de casier judiciaire, et l'état d'adhésion sur l'ensemble du territoire national.
                @endif
            </div>
        </div>
    </div>

    <!-- Prescriptions -->
    <div class="prescriptions-section">
        <div class="prescription-title">2 - Prescriptions :</div>
        
        @if(isset($prescriptions) && count($prescriptions) > 0)
            @foreach($prescriptions as $prescription)
                <div class="prescription-content">
                    {{ $prescription }}
                </div>
            @endforeach
        @else
            <div class="prescription-content">
                Toute modification majeure intervenue au niveau des structures ou des programmes d'un parti politique, notamment sur la dénomination, 
                les statuts ; le règlement intérieur, le siège, l'emblème ou le logo, les organes dirigeants, doit être notifiée pour information 
                aux services compétents du Ministère de l'Intérieur dans un délai de quinze (15) jours à compter de la date de la modification concernée.
            </div>
            
            <div class="prescription-content">
                Le Directoire du parti est tenu d'avoir une comptabilité régulière et un inventaire de ses biens meubles et immeubles, 
                de justifier auprès de la Cour des Comptes l'utilisation des subventions et de se conformer aux dispositions en vigueur 
                en matière de transfert de fonds à l'étranger.
            </div>
        @endif
    </div>

    <!-- Section signature -->
    <div class="signature-section">
        <div class="signature-location">
            Fait à Libreville, le {{ $date_generation ?? now()->format('d/m/Y') }}
        </div>
        
        <div class="minister-title">
            <strong>Le Ministre de l'Intérieur, de la Sécurité<br>et de la Décentralisation</strong>
        </div>
        
        <div class="minister-name">
            <strong>{{ $ministre_nom ?? 'Hermann IMMONGAULT' }}</strong>
        </div>
    </div>

    <!-- Ampliations -->
    <div class="ampliations-section">
        <u>AMPLIATIONS :</u>
        <div class="ampliations-list">
            <div class="ampliation-item">
                <span>PR</span><span>2</span>
            </div>
            <div class="ampliation-item">
                <span>VPG</span><span>2</span>
            </div>
            <div class="ampliation-item">
                <span>MEF</span><span>2</span>
            </div>
            <div class="ampliation-item">
                <span>CND</span><span>6</span>
            </div>
            <div class="ampliation-item">
                <span>MISD</span><span>10</span>
            </div>
            <div class="ampliation-item">
                <span>J.O</span><span>2</span>
            </div>
            <div class="ampliation-item">
                <span>PARTI</span><span>2</span>
            </div>
            <div class="ampliation-item">
                <span>CHRONO</span><span>5</span>
            </div>
            <div class="ampliation-item">
                <span>ARCHIVES</span><span>10</span>
            </div>
        </div>
    </div>

    <!-- Copie -->
    <p style="margin-top: 15px; font-size: 11pt;">
        Copie : J.O
    </p>

    <!-- ✅ SECTION QR CODE HARMONISÉE AVEC L'ACCUSÉ ET LE RÉCÉPISSÉ PROVISOIRE -->
    <div class="qr-section">
        <div class="qr-content">
            <div class="qr-left">
                <div class="qr-box">
                    @if(isset($qr_code) && $qr_code)
                        @php
                            // Utiliser le service QrCodeService avec méthode optimisée (IDENTIQUE AUX AUTRES)
                            $qrService = app(\App\Services\QrCodeService::class);
                            $qrBase64 = $qrService->getQrCodeForPdf($qr_code);
                        @endphp

                        {{-- ✅ QR CODE EN BASE64 (solution optimale) --}}
                        @if($qrBase64)
                            <img src="{{ $qrBase64 }}" 
                                 alt="QR Code de vérification" 
                                 width="100" 
                                 height="100" 
                                 class="qr-image">
                            <div class="qr-text">Vérification en ligne</div>
                            <div class="qr-code-id">{{ $qr_code->code }}</div>
                            
                        {{-- ✅ FALLBACK: SVG si base64 échoue --}}
                        @elseif(!empty($qr_code->svg_content))
                            <div style="width: 100px; height: 100px; margin: 0 auto 10px auto; overflow: hidden;">
                                {!! str_replace(['width="150"', 'height="150"'], ['width="100"', 'height="100"'], $qr_code->svg_content) !!}
                            </div>
                            <div class="qr-text">Vérification en ligne</div>
                            <div class="qr-code-id">{{ $qr_code->code }}</div>
                            
                        {{-- ✅ FALLBACK: Placeholder si tout échoue (couleur bleue pour récépissé définitif) --}}
                        @else
                            <svg width="100" height="100" style="margin: 0 auto 10px auto; display: block;">
                                <rect width="100" height="100" fill="#f8f9fa" stroke="#003f7f" stroke-width="2"/>
                                <text x="50" y="30" font-family="Arial" font-size="8" text-anchor="middle" fill="#003f7f">QR Code</text>
                                <text x="50" y="45" font-family="Arial" font-size="7" text-anchor="middle" fill="#666">Disponible</text>
                                <text x="50" y="60" font-family="Arial" font-size="6" text-anchor="middle" fill="#666">en ligne</text>
                                <text x="50" y="75" font-family="Arial" font-size="5" text-anchor="middle" fill="#999">{{ $qr_code->code }}</text>
                            </svg>
                            <div class="qr-text">Vérification en ligne</div>
                            <div class="qr-code-id">{{ $qr_code->code }}</div>
                        @endif

                        {{-- URL de vérification --}}
                        @if(!empty($qr_code->verification_url))
                            <div class="qr-url">{{ $qr_code->verification_url }}</div>
                        @endif
                        
                    @else
                        {{-- Pas de QR code --}}
                        <svg width="100" height="100" style="margin: 0 auto 10px auto; display: block;">
                            <rect width="100" height="100" fill="#f8f9fa" stroke="#999" stroke-width="1" stroke-dasharray="4,4"/>
                            <text x="50" y="40" font-family="Arial" font-size="8" text-anchor="middle" fill="#666">QR Code</text>
                            <text x="50" y="55" font-family="Arial" font-size="7" text-anchor="middle" fill="#666">En cours</text>
                            <text x="50" y="70" font-family="Arial" font-size="6" text-anchor="middle" fill="#999">de génération...</text>
                        </svg>
                        <div class="qr-text">Vérification en ligne</div>
                        <div class="qr-code-id">En cours...</div>
                    @endif
                </div>
            </div>

            <!-- Footer à droite (harmonisé) -->
            <div class="qr-right">
                <div class="footer-content">
                    <strong>République Gabonaise</strong><br>
                    Ministère de l'Intérieur, de la Sécurité et de la Décentralisation<br>
                    <em>Système de Gestion et de Légalisation des Partis politiques et autres Organisations</em>
                </div>
            </div>
        </div>
    </div>

</body>
</html>