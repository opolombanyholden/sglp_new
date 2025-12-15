@extends('documents.layouts.official')

@section('content')
    <div class="document-number">
        N° {{ $document['numero_document'] }}
    </div>

    <div class="document-title">
        RÉCÉPISSÉ PROVISOIRE
    </div>



    <div class="content">
        <p class="mb-20">
            Nous soussignés, Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,
            attestons que <strong>{{ $organisation['president_nom'] ?? 'Monsieur/Madame [Nom du Président]' }}</strong>
            de nationalité Gabonaise, Président(e) de l'association à but non lucratif,
            œuvrant dans le domaine du <strong>{{ $organisation['domaine'] ?? 'Social' }}</strong> dénommée :
        </p>

        <p class="text-center bold mb-30" style="font-size: 13pt;">
            « {{ strtoupper($organisation['nom']) }} »
        </p>

        <p class="mb-20">
            Dont le siège social est fixé à <strong>{{ $organisation['siege_social'] }}</strong>,
            Téléphone : <strong>{{ $organisation['telephone'] ?? 'Non renseigné' }}</strong>,
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