{{-- En-tête avec numéro de dossier (le logo et header_text sont dans SetHTMLHeader) --}}
<table class="header-table" style="width:100%; margin-bottom:20px; border-collapse:collapse;">
    <tr>
        <td class="header-left"
            style="color:#000; font-weight:bold; font-size:14px; vertical-align:top; width:400px; text-align:left;">
            <div style="font-size:18px; font-weight:bold; margin-top:0;">
                N° {{ $numero_administratif ?? 'XXXX/MISD/SG/DGELP/DPPALC' }}
            </div>
        </td>
        <td width="70"></td>
        <td class="header-right"
            style="color:#003f7f; font-weight:bold; font-size:12px; text-align:center; vertical-align:top;"></td>
    </tr>
</table>

{{-- Titre --}}
<div style="text-align:center; font-size:16px; margin:30px 0;">
    <h1
        style="color:#009e3f; border:2px solid #009e3f; padding:5px 15px; text-align:center; display:inline-block; margin:20px 0;">
        ACCUSÉ DE RÉCEPTION DE DOSSIER<br>
        DE DÉCLARATION {{ strtoupper($type_organisation ?? 'ORGANISATION') }}
    </h1>
</div>

{{-- Contenu principal --}}
<div class="main-content" style="text-align:justify; line-height:1.8; margin:30px 0;">
    Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,<br><br>

    Agissant conformément à ses attributions en matière de déclaration
    {{ ($type_organisation ?? 'organisation') == 'parti_politique' ? 'de partis politiques' : 'd\'organisations' }},
    atteste que {{ $civilite ?? 'Monsieur/Madame' }}
    <strong>{{ $nom_prenom ?? 'NOM PRÉNOM' }}</strong>, de nationalité {{ $nationalite ?? 'gabonaise' }},
    domicilié à {{ $domicile ?? 'ADRESSE' }},

    @if(isset($telephone) && $telephone !== 'Non renseigné')
        Téléphone : <span>{{ $telephone }}</span>,
    @else
        @if(isset($org_telephone) && $org_telephone !== 'Non renseigné')
            Téléphone : <span>{{ $org_telephone }}</span>,
        @endif
    @endif

    a déposé, aux services du Ministère, un dossier complet de déclaration
    {{ ($type_organisation ?? 'organisation') == 'parti_politique' ? 'du parti politique' : 'de l\'organisation' }}
    dénommé<strong>{{ isset($sigle_organisation) && $sigle_organisation ? 'e' : '' }}</strong>
    <span>{{ $nom_organisation ?? 'NOM ORGANISATION' }}{{ isset($sigle_organisation) && $sigle_organisation ? ' (' . $sigle_organisation . ')' : '' }}</span>
    conformément aux dispositions de la loi n°016/2025 du 27 juin 2025 relative aux
    {{ ($type_organisation ?? 'organisation') == 'parti_politique' ? 'partis politiques' : 'organisations' }}
    en République Gabonaise.<br>

    En foi de quoi le présent accusé de réception lui est délivré pour servir et faire valoir ce que de droit.
</div>

{{-- Date et lieu --}}
<div class="date-location" style="text-align:right; margin-top:30px; font-style:italic;">
    Fait à Libreville, le ..........................
</div>

{{-- Signature --}}
<div class="signature-section" style="margin-top:50px; text-align:right;">
    Le Ministre<br><br><br><br>
    <strong>Hermann IMMONGAULT</strong>
</div>

{{-- Copie --}}
<p style="margin-top:15px; font-size:11pt;">
    <u>Copies :</u>
<p>- SG (MISD)<br />
    - CND
</p>
</p>

{{-- Section QR Code (PNG base64) - TEMPORAIREMENT DÉSACTIVÉE POUR TEST --}}
{{--
<div style="text-align:center; margin-top:30px; page-break-inside:avoid;">
    <p style="font-size:12px; margin-bottom:10px;">
        <strong>Code de vérification du document</strong>
    </p>

    @if(!empty($qr_code_png))
        <div style="display:inline-block; padding:10px; border:1px solid #ddd; background:#fff;">
            <img src="{{ $qr_code_png }}"
alt="QR Code de vérification"
style="width:120px; height:120px; display:block;" />
</div>

<p style="font-size:10px; color:#666; margin-top:10px;">
    Scannez ce code pour vérifier l'authenticité du document
</p>

@if(!empty($verification_url))
<p style="font-size:9px; color:#999; margin-top:5px;">
    ou visitez : {{ $verification_url }}
</p>
@endif
@else
<p style="color:#999; font-size:10px;">Code QR non disponible</p>
@endif
</div>
--}}

{{-- Signature personnalisée (WYSIWYG) --}}
@if(!empty($signature_text))
    <div class="document-signature"
        style="margin-top: 30px; padding: 15px; border-top: 1px solid #ddd; page-break-inside: avoid;">
        {!! $signature_text !!}
    </div>
@endif

{{-- ⚠️ QR Code Section TEMPORAIREMENT DÉSACTIVÉE POUR TEST --}}
{{--
<div class="qr-section" style="margin-top:40px; padding-top:20px; width:120px;">
    <div class="qr-content">
        <div class="qr-left" style="width:150px; vertical-align:top; padding:10px;">
            <div class="qr-box" style="text-align:center; width:100px;">
                @if(isset($qr_code) && $qr_code)
                    @php
                        $qrService = app(\App\Services\QrCodeService::class);
                        $qrBase64 = $qrService->getQrCodeBase64ForPdf($qr_code);
                    @endphp

                    @if($qrBase64)
                        <img src="{{ $qrBase64 }}" alt="QR Code" width="100" height="100" style="display:block;
margin:auto;">
@else
<div
    style="width:100px; height:100px; border:2px solid #003f7f; margin:0 auto; display:flex; align-items:center; justify-content:center; text-align:center;">
    <div style="font-size:8pt; color:#003f7f;">
        QR Code<br>
        <small style="font-size:6pt;">{{ isset($qr_code->code) ? substr($qr_code->code, 0, 10) : 'N/A' }}</small>
    </div>
</div>
@endif
@else
<div
    style="width:100px; height:100px; border:1px dashed #999; margin:0 auto; display:flex; align-items:center; justify-content:center; text-align:center;">
    <span style="font-size:7pt; color:#666;">QR Code<br>En attente</span>
</div>
@endif
</div>
</div>
</div>
</div>
--}}