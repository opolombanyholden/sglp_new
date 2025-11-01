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
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .logo-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .logo-left {
            width: 120px;
            height: 80px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #666;
        }
        
        .logo-right {
            width: 80px;
            height: 50px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #666;
        }
        
        .header-text {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            line-height: 1.2;
            flex: 1;
            margin: 0 20px;
        }
        
        .ministry-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .department-divider {
            margin: 2px 0;
            border-bottom: 1px solid #000;
            width: 150px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .reference-number {
            text-align: center;
            margin: 20px 0;
            font-size: 10pt;
            font-weight: bold;
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

        .qr-container {
            position: relative;
            min-height: 120px;
        }
        
        .qr-code-box {
            border: 1px solid #009e3f;
            padding: 5px;
            background: white;
            text-align: center;
            display: table;
            margin: 0;
        }
        
        .qr-code-box svg {
            max-width: 100px;
            max-height: 100px;
        }
        
        .qr-verification-text {
            font-size: 8pt;
            color: #009e3f;
            font-weight: bold;
            margin-top: 3px;
        }

        .footer {
            margin-top: 20px;
            font-size: 9pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- En-tête officiel conforme au document Word -->
       <!-- En-tête amélioré -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                
                
                <div style="font-size:14px; font-weight: bold; margin-top:40px;">
                    N° {{ $numero_administratif ?? 'XXXX/MISD/SG/DGELP/DPPALC' }}
                </div>
            </td>
            <td width="70"></td>
            <td class="header-right">
                
            </td>
        </tr>
    </table>

    <!-- Titre du document conforme -->
    <div class="document-title">
        RÉCÉPISSÉ DÉFINITIF DE LÉGALISATION
        <div style="width:70%; margin-left:15%; height:4px; background-color:#F00;"></div>
    </div>

    <!-- Contenu principal conforme au document Word -->
    <div class="content">
        <p><strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong></p>
        
        <p>
            Agissant conformément à ses attributions en matière de Libertés Publiques, délivre aux personnes ci-après désignées, 
            un récépissé définitif de déclaration de Parti politique, conformément <strong>à la loi n°016/2025 du 27 juin 2025 
            relative aux partis politiques en République Gabonaise</strong>.
        </p>
    </div>

    <!-- Détails de l'organisation conformes -->
    <div class="organization-details">
        <div class="detail-line">
            <span class="highlight"><u>Dénomination :</u></span> 
            <span class="highlight">« {{ $nom_organisation }} »</span>{{ $sigle_organisation ? ', en abrégé ' : '' }}<span class="highlight">{{ $sigle_organisation ?? '' }}</span>
        </div>
        
        <div class="detail-line">
            <span class="highlight"><u>Siège Social</u> :</span> {{ $adresse_siege ?? 'Libreville, GABON' }} ; <strong>BP : {{ $boite_postale ?? '' }} ; Tél : {{ $telephone_organisation ?? '' }}.</strong>
        </div>
    </div>

    <!-- Section Directoire conforme au document Word -->
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
            <div class="dirigeant-line">
                • <span class="highlight"><u>Président-Fondateur :</u></span> {{ $president_fondateur ?? 'Brice Clotaire OLIGUI NGUEMA' }} ;
            </div>
            <div class="dirigeant-line">
                • <span class="highlight"><u>Secrétaire Général :</u></span> {{ $secretaire_general ?? 'Mays Lloyd MOUISSI' }} ;
            </div>
            <div class="dirigeant-line">
                • <span class="highlight"><u>Trésorier :</u></span> {{ $tresorier ?? 'Aurélien MINTSA MI NGUEMA' }}.
            </div>
        @endif
    </div>

    <!-- Pièces annexées conformes au document Word -->
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

    <!-- Prescriptions conformes au document Word -->
    <div class="prescriptions-section">
        <div class="prescription-title">2 - Prescriptions :</div>
        
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
    </div>

    <!-- Section signature conforme -->
    <div class="signature-section">
        <div class="signature-location">
            Fait à Libreville, le {{ $date_generation ?? '24 Juillet 2025' }}
        </div>
        
        <div class="minister-title">
            <strong>Le Ministre de l'Intérieur, de la Sécurité<br>et de la Décentralisation</strong>
        </div>
        
        <div class="minister-name">
            <strong>{{ $ministre_nom ?? 'Hermann IMMONGAULT' }}</strong>
        </div>
    </div>

    <!-- Ampliations conformes au document Word -->
    <div class="ampliations-section">
        <u>Copies :</u> 
        <p>- SG (MISD)<br/>
           - CND
        </p>
    </div>

    <!-- Zone QR Code et Footer -->
    <div style="position: relative; margin-top: 30px;">
        <!-- QR Code à gauche -->
        <div style="position: absolute; left: 0; bottom: 50px; width: 120px;">
            @if(isset($qr_code) && $qr_code)
                <div class="qr-code-box">
                    {!! $qr_code->svg_content ?? '<div style="width:100px;height:100px;background:#f0f0f0;border:1px dashed #999;display:flex;align-items:center;justify-content:center;font-size:10px;">QR Code</div>' !!}
                    <div class="qr-verification-text">
                        Vérification en ligne
                    </div>
                </div>
            @else
                <div style="width: 110px; height: 110px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999; background: #fafafa;">
                    QR Code<br>en cours
                </div>
            @endif
        </div>

        
    </div>
</body>
</html>