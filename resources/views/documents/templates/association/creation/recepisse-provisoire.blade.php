@extends('documents.layouts.official')

@section('content')
    <div class="document-number" style="padding-left:40px; font-size:8pt; margin-top:0pt; padding-top:0pt;">
        N° {{ $document['numero_document'] }}/MISD/SG/DGELP/DPPALC
    </div>

    <div class="document-title">
        RÉCÉPISSÉ PROVISOIRE
        <div class="document-title-bar"></div>
    </div>



    <div class="content">
        <p class="mb-20">
            Nous soussignés, Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,
            attestons que <strong>Monsieur/Madame
                {{ $organisation['president_nom'] ?? '[Nom du Président]' }}</strong>
            de nationalité Gabonaise, <span
                style="text-transform:capitalize;"><strong>{{ $organisation['president_fonction'] ?? 'Président(e)' }}</strong></span>
            de
            l'association à but non
            lucratif,
            œuvrant dans le domaine du <strong>{{ $organisation['domaine'] ?? 'Social' }}</strong> dénommée :
        </p>

        <p class="text-center bold mb-30" style="font-size: 13pt;">
            « {{ strtoupper($organisation['nom']) }} »
        </p>

        <p class="mb-20">
            Dont le siège social est fixé à <strong>{{ $organisation['siege_social'] }}</strong>
            @if(!empty($organisation['quartier']))
                , Quartier <strong>{{ $organisation['quartier'] }}</strong>
            @endif
            @if(!empty($organisation['ville_commune']))
                , <strong>{{ $organisation['ville_commune'] }}</strong>
            @endif
            , Téléphone : <strong>{{ $organisation['telephone'] ?? 'Non renseigné' }}</strong>,
            a déposé à nos services un dossier complet visant à obtenir un récépissé définitif
            de déclaration d'association conformément aux dispositions de la loi n° 35/62 du 10 décembre 1962
            relative aux associations en République Gabonaise.
        </p>

        <p class="mb-20">
            En foi de quoi, le présent récépissé est délivré à l'intéressé(e) pour servir et valoir ce que de droit.
        </p>
    </div>

    <div class="signature-block">
        @include('documents.components.signature')
    </div>

@endsection