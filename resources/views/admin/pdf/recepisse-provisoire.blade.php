<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récépissé Provisoire - {{ $organisation->nom ?? $nom_organisation }}</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            margin: 50px;
            position: relative;
        }
        
        h1 {
            color: #009e3f;
            border: 2px solid #009e3f;
            padding: 5px 15px;
            text-align: center;
            display: inline-block;
            margin: 20px 0;
        }
        
        /* ===== STYLES QR CODE HARMONISÉS AVEC L'ACCUSÉ ===== */
        .qr-section {
            margin-top:40px;
            margin-left: 0px;
            padding-top:0px;
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
            padding:0px;
        }

        .qr-right {
            display: table-cell;
            vertical-align: top;
            text-align: center;
            padding:0px;
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
        
        /* En-tête harmonisé */
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
        
        /* Contenu principal */
        .main-content {
            text-align: justify;
            line-height: 1.8;
            margin: 30px 0;
        }
        
        /* Signature */
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        
        .date-location {
            text-align: right;
            margin-top: 30px;
            font-style: italic;
        }
        
        /* Titre du document */
        .document-title {
            text-align: center;
            font-size: 16px;
            margin: 25px 0;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    
    <!-- En-tête harmonisé avec l'accusé -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div style="font-size:18px; font-weight: bold; margin-top:150px;">
                    N° {{ $numero_administratif ?? $numero_reference ?? 'XXXX/MISD/SG/DGELP/DPPALC' }}
                </div>
            </td>
            <td width="70"></td>
            <td class="header-right"></td>
        </tr>
    </table>

    <!-- Titre principal -->
    <div class="document-title">
        <h1>
            RÉCÉPISSÉ PROVISOIRE
            DE LÉGALISATION
        </h1>
    </div>

    <!-- Contenu principal avec variables harmonisées -->
    <div class="main-content">
        Je soussigné, Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,<br><br>

        atteste que {{ $civilite ?? 'Monsieur' }} 
        <strong>{{ $nom_prenom ?? 'NOM PRÉNOM' }}</strong>, 
        de nationalité {{ $nationalite ?? 'gabonaise' }},
        domicilié à {{ $domicile ?? 'ADRESSE' }}, 
        
        {{-- ✅ CORRECTION : Affichage conditionnel du téléphone (IDENTIQUE À L'ACCUSÉ) --}}
        @if(isset($telephone) && $telephone !== 'Non renseigné')
            Téléphone : <span class="">{{ $telephone }}</span>,
        @else
            {{-- Essayer le téléphone de l'organisation comme fallback --}}
            @if(isset($org_telephone) && $org_telephone !== 'Non renseigné')
                Téléphone : <span class="">{{ $org_telephone }}</span>,
            @endif
        @endif
        
        <strong>{{ $fonction_representant ?? $fonction_dirigeant ?? 'Secrétaire Général' }}</strong> du Parti politique dénommé 
        <strong>« {{ strtoupper($organisation->nom ?? $nom_organisation ?? 'NOM ORGANISATION') }} »</strong>{{ isset($sigle_organisation) && $sigle_organisation ? ', en abrégé ' : '' }}<strong>{{ $sigle_organisation ?? '' }}</strong>, 
        dont le siège est fixé à {{ $adresse_siege ?? 'Libreville' }}, {{ $boite_postale ? 'BP : ' . $boite_postale . ', ' : '' }}a déposé un dossier auprès des services compétents du Ministère de l'Intérieur en vue de sa légalisation, 
        suivant l'accusé de réception n°{{ $numero_accuse_reception ?? '001' }} du {{ $date_accuse_reception ?? '11 juillet 2025' }}, 
        et jugé conforme aux prescriptions <strong>de la loi n°016/2025 du 27 juin 2025 relative aux Partis politiques en République Gabonaise</strong>.<br><br>

        En application de <strong>l'article 28</strong> de la loi relative aux partis politiques susmentionnée, 
        le présent récépissé est délivré à l'intéressé{{ ($civilite ?? '') === 'Madame' ? 'e' : '' }}, en sa qualité de représentant du Parti politique 
        <strong>« {{ strtoupper($organisation->nom ?? $nom_organisation ?? 'NOM ORGANISATION') }} »</strong>, pour servir et valoir ce que de droit.
    </div>

    <!-- Date et lieu -->
    <div class="date-location">
        Fait à Libreville, le {{ $date_generation ?? now()->format('d/m/Y') }}
    </div>

    <!-- Signature -->
    <div class="signature-section">
        Le Ministre<br><br><br><br>
        <strong>Hermann IMMONGAULT</strong>
    </div>

    <!-- Copie -->
    <p style="margin-top: 15px; font-size: 11pt;">
        <u>Copies :</u> 
        <p>- SG (MISD)<br/>
           - CND
        </p>
    </p>

    <!-- ✅ SECTION QR CODE HARMONISÉE AVEC L'ACCUSÉ DE RÉCEPTION -->
    <div class="qr-section">
        <div class="qr-content">
            <div class="qr-left">
                <div class="qr-box">
                    @if(isset($qr_code) && $qr_code)
                        @php
                            // Utiliser le service QrCodeService avec méthode optimisée
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
                            <div class="qr-text"></div>
                            <div class="qr-code-id"></div>
                            
                        {{-- ✅ FALLBACK: SVG si base64 échoue --}}
                        @elseif(!empty($qr_code->svg_content))
                            <div style="width: 100px; height: 100px; margin: 0 auto 10px auto; overflow: hidden;">
                                {!! str_replace(['width="150"', 'height="150"'], ['width="100"', 'height="100"'], $qr_code->svg_content) !!}
                            </div>
                            <div class="qr-text">Vérification en lign 2</div>
                            <div class="qr-code-id">{{ $qr_code->code }}</div>
                            
                        {{-- ✅ FALLBACK: Placeholder si tout échoue --}}
                        @else
                            <svg width="100" height="100" style="margin: 0 auto 10px auto; display: block;">
                                <rect width="100" height="100" fill="#f8f9fa" stroke="#003f7f" stroke-width="2"/>
                                <text x="50" y="30" font-family="Arial" font-size="8" text-anchor="middle" fill="#003f7f">QR Code</text>
                                <text x="50" y="45" font-family="Arial" font-size="7" text-anchor="middle" fill="#666">Disponible</text>
                                <text x="50" y="60" font-family="Arial" font-size="6" text-anchor="middle" fill="#666">en ligne</text>
                                <text x="50" y="75" font-family="Arial" font-size="5" text-anchor="middle" fill="#999">{{ $qr_code->code }}</text>
                            </svg>
                            <div class="qr-text"></div>
                            <div class="qr-code-id"></div>
                        @endif

                        {{-- URL de vérification --}}
                        @if(!empty($qr_code->verification_url))
                            <div class="qr-url"></div>
                        @endif
                        
                    @else
                        {{-- Pas de QR code --}}
                        <svg width="100" height="100" style="margin: 0 auto 10px auto; display: block;">
                            <rect width="100" height="100" fill="#f8f9fa" stroke="#999" stroke-width="1" stroke-dasharray="4,4"/>
                            <text x="50" y="40" font-family="Arial" font-size="8" text-anchor="middle" fill="#666">QR Code</text>
                            <text x="50" y="55" font-family="Arial" font-size="7" text-anchor="middle" fill="#666">En cours</text>
                            <text x="50" y="70" font-family="Arial" font-size="6" text-anchor="middle" fill="#999">de génération...</text>
                        </svg>
                        <div class="qr-text"></div>
                        <div class="qr-code-id"></div>
                    @endif
                </div>
            </div>

           
        </div>
    </div>

</body>
</html>