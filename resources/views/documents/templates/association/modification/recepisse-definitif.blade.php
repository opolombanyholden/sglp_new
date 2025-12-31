@extends('documents.layouts.official')

@section('content')
    <div class="document-number" style="padding-left:40px; font-size:8pt; margin-top:0pt; padding-top:0pt;">
        N° {{ $document['numero_document'] }}/MISD/SG/DGELP/DPPALC
    </div>

    <div class="document-title" style="font-size:20px; margin-top:45px;">
        RÉCÉPISSÉ DE DÉCLARATION DE MODIFICATION
        <div class="document-title-bar-vert"></div>
    </div>

    @php
        // Les données de modifications sont passées par DocumentGenerationService dans la variable $modifications
        // Les nouvelles valeurs ont été appliquées à $organisation par le service

        $modifData = $modifications ?? [];
        $typeModification = $modifData['type_modification'] ?? null;
        $justification = $modifData['justification'] ?? null;
        $champsModifies = $modifData['modifications'] ?? [];
        $bureauModifications = $modifData['bureau_modifications'] ?? [];

        // Détecter les types de modifications basé sur les champs réellement modifiés
        $typesModifies = [];
        if (!empty($champsModifies['nom']) || !empty($champsModifies['sigle'])) {
            $typesModifies['denomination'] = true;
        }
        if (!empty($bureauModifications)) {
            $typesModifies['bureau'] = true;
        }
        if (!empty($champsModifies['telephone']) || !empty($champsModifies['telephone_secondaire']) || !empty($champsModifies['email'])) {
            $typesModifies['contact'] = true;
        }
        if (!empty($champsModifies['siege_social']) || !empty($champsModifies['ville_commune']) || !empty($champsModifies['quartier'])) {
            $typesModifies['adresse'] = true;
        }
        if (!empty($champsModifies['objet'])) {
            $typesModifies['objet'] = true;
        }
    @endphp

    <div class="content" style="font-size:12pt; text-align: justify;">
        <p class="mb-20">
            <strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong>
        </p>

        <p class="mb-20" style="text-align: justify;">
            En application des dispositions de l'article 41 de la Loi n° 016/2025 du 27 juin 2025 relative aux
            @if(($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique')
                partis politiques
            @else
                associations
            @endif,
            délivre
            {{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? 'au Parti politique' : 'à l\'Association' }}
            dénommé{{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? '' : 'e' }}
            « <strong>{{ strtoupper($organisation['nom']) }}</strong>
            @if(!empty($organisation['sigle']))
                ({{ strtoupper($organisation['sigle']) }})
            @endif
            »,
            @if(!empty($organisation['numero_recepisse']))
                légalisé{{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? '' : 'e' }} sous
                le récépissé définitif
                n° {{ $organisation['numero_recepisse'] }}
                @if(!empty($organisation['date_recepisse']))
                    daté du {{ \Carbon\Carbon::parse($organisation['date_recepisse'])->translatedFormat('d F Y') }}
                @endif,
            @endif
            le présent récépissé de déclaration de modification.
        </p>

        <p class="mb-20" style="text-align: justify;">
            Les modifications intervenues au sein
            {{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? 'dudit parti politique' : 'de ladite association' }}
            concernent les informations suivantes :
        </p>

        {{-- Liste des types de modifications avec cases à cocher --}}
        <div style="margin-left: 20px; margin-bottom: 15px;">
            <p>
                <span style="font-family: Wingdings;">{{ isset($typesModifies['denomination']) ? 'þ' : 'o' }}</span>
                Dénomination
            </p>
            <p>
                <span style="font-family: Wingdings;">{{ isset($typesModifies['bureau']) ? 'þ' : 'o' }}</span>
                Bureau
            </p>
            <p>
                <span style="font-family: Wingdings;">{{ isset($typesModifies['contact']) ? 'þ' : 'o' }}</span>
                Contact
            </p>
            <p>
                <span style="font-family: Wingdings;">{{ isset($typesModifies['adresse']) ? 'þ' : 'o' }}</span>
                Adresse
            </p>
            @if(isset($typesModifies['objet']))
                <p>
                    <span style="font-family: Wingdings;">þ</span>
                    Objet
                </p>
            @endif
        </div>

        <p class="mb-20" style="margin-top: 20px;">
            <strong><u>Nouvelles informations :</u></strong>
        </p>

        {{-- Dénomination - Les nouvelles valeurs sont déjà appliquées à $organisation --}}
        <p class="mb-10" style="text-align: justify;">
            <strong>Dénomination :</strong>
            {{ strtoupper($organisation['nom']) }}
            @if(!empty($organisation['sigle']))
                ({{ strtoupper($organisation['sigle']) }})
            @endif
        </p>

        {{-- Bureau - Utiliser les nouveaux membres du bureau --}}
        <p class="mb-10">
            <strong>Bureau :</strong>
        </p>
        <div style="margin-left: 20px; margin-bottom: 10px;">
            @if(!empty($bureauModifications))
                @foreach($bureauModifications as $membre)
                    <p style="margin-bottom: 2px;">
                        {{ $membre['fonction'] ?? 'Membre' }} :
                        {{ strtoupper(($membre['nom'] ?? '') . ' ' . ($membre['prenom'] ?? '')) }} ;
                    </p>
                @endforeach
            @elseif(isset($organisation_membres) && count($organisation_membres) > 0)
                @foreach($organisation_membres as $membre)
                    <p style="margin-bottom: 2px;">
                        {{ $membre['fonction'] ?? 'Membre' }} : {{ strtoupper($membre['nom_complet'] ?? ($membre['nom'] ?? '')) }} ;
                    </p>
                @endforeach
            @else
                <p style="margin-bottom: 2px;">
                    {{ $organisation['president_fonction'] ?? 'Président' }} :
                    {{ strtoupper($organisation['president_nom'] ?? '[Nom du Président]') }} ;
                </p>
            @endif
        </div>

        {{-- Contact - Les nouvelles valeurs sont dans $organisation --}}
        <p class="mb-10" style="text-align: justify;">
            <strong>Contact :</strong>
            @if(!empty($organisation['telephone']))
                {{ $organisation['telephone'] }}
            @endif
            @if(!empty($organisation['telephone_2']))
                / {{ $organisation['telephone_2'] }}
            @endif
            @if(!empty($organisation['email']))
                / {{ $organisation['email'] }}
            @endif
            .
        </p>

        {{-- Adresse - Les nouvelles valeurs sont dans $organisation --}}
        <p class="mb-20" style="text-align: justify;">
            <strong>Adresse :</strong>
            @if(!empty($organisation['ville_commune']))
                {{ $organisation['ville_commune'] }}
            @endif
            @if(!empty($organisation['quartier']))
                , {{ $organisation['quartier'] }}
            @endif
            @if(!empty($organisation['siege_social']))
                , {{ $organisation['siege_social'] }}
            @endif
            - Gabon.
        </p>

        {{-- Pièces Annexées --}}
        <p class="mb-10" style="margin-top: 20px;">
            <strong><u>Pièces Annexées :</u></strong>
        </p>

        <ul style="margin-left: 30px; margin-bottom: 15px; font-size: 12pt; line-height: 1.6;">
            <li>Procès-verbal d'Assemblée Générale Extraordinaire ;</li>
            <li>Récépissé définitif de déclaration ;</li>
            @if(!empty($bureauModifications))
                <li>La liste actualisée des membres du comité directeur ;</li>
            @endif
            @if(isset($typesModifies['denomination']) || isset($typesModifies['objet']))
                <li>Statuts modifiés.</li>
            @endif
        </ul>
    </div>

    <div class="signature-block" style="margin-top: 30px; padding-top: 10px;">
        @include('documents.components.signature')
    </div>

@endsection