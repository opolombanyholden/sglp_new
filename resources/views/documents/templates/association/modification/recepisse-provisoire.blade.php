@extends('documents.layouts.official')

@section('content')
    <div class="document-number" style="padding-left:40px; font-size:8pt; margin-top:0pt; padding-top:0pt;">
        N° {{ $document['numero_document'] }}/MISD/SG/DGELP/DPPALC
    </div>

    <div class="document-title">
        RÉCÉPISSÉ PROVISOIRE DE MODIFICATION
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
            de déclaration de <strong>modification</strong> conformément aux dispositions de la loi n° 35/62 du 10 décembre
            1962
            relative aux associations en République Gabonaise.
        </p>

        {{-- Section spécifique aux modifications demandées --}}
        @if(isset($modifications) && !empty($modifications))
            <p class="mb-20">
                <strong><u>Nature des modifications demandées :</u></strong>
            </p>

            <div style="margin-left: 20px; margin-bottom: 15px;">
                @if(isset($modifications['type_modification']))
                    <p><strong>Type :</strong> {{ ucfirst(str_replace('_', ' ', $modifications['type_modification'])) }}</p>
                @endif

                @if(isset($modifications['justification']))
                    <p><strong>Motif :</strong> {{ $modifications['justification'] }}</p>
                @endif

                {{-- Résumé des modifications --}}
                @php
                    $nbModifs = 0;
                    if (isset($modifications['modifications']) && is_array($modifications['modifications'])) {
                        $nbModifs += count(array_filter($modifications['modifications'], fn($v) => !empty($v) && !is_array($v)));
                    }
                    if (isset($modifications['articles_modifies'])) {
                        $nbModifs += count($modifications['articles_modifies']);
                    }
                    if (isset($modifications['bureau_modifications'])) {
                        $nbModifs += count($modifications['bureau_modifications']);
                    }
                @endphp

                @if($nbModifs > 0)
                    <p><strong>Nombre d'éléments modifiés :</strong> {{ $nbModifs }}</p>
                @endif
            </div>
        @endif

        <p class="mb-20">
            En foi de quoi, le présent récépissé provisoire de modification est délivré à l'intéressé(e) pour servir et
            valoir ce que de droit.
        </p>
    </div>

    <div class="signature-block">
        @include('documents.components.signature')
    </div>

@endsection