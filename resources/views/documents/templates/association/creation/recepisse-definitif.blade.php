@extends('documents.layouts.official')

@section('content')
    <div class="document-number" style="padding-left:40px; font-size:8pt; margin-top:0pt; padding-top:0pt;">
        N° {{ $document['numero_document'] }}/MISD/SG/DGELP/DPPALC
    </div>

    <div class="document-title" style="font-size:20px; margin-top:45px;">
        RÉCÉPISSÉ DÉFINITIF DE DÉCLARATION D'ASSOCIATION
        <div class="document-title-bar-vert"></div>
    </div>

    <div class="content" style="font-size:12pt">
        <p class="mb-20">
            <strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong>
            <br />
            Agissant conformément à ses attributions en matière d'associations donne, aux personnes ci-après désignées,
            récépissé définitif de déclaration pour l'association définie comme suit, régie par la loi n°35/62 du
            10 décembre 1962.
        </p>

        <p class="mb-20">
            <strong><u>Dénomination de l'Association</u> :</strong> {{ strtoupper($organisation['nom']) }}
            @if(!empty($organisation['sigle']))
                ({{ strtoupper($organisation['sigle']) }})
            @endif
        </p>

        <p class="mb-20">
            <strong><u>Objet</u> :</strong><br />
            {{ $organisation['objet'] ?? 'Cette association à but non lucratif a pour objectif...' }}
        </p>

        <p class="mb-20">
            <strong><u>Siège Social</u> :</strong> {{ $organisation['siege_social'] }}
            @if(!empty($organisation['quartier']))
                , Quartier {{ $organisation['quartier'] }}
            @endif
            @if(!empty($organisation['ville_commune']))
                , {{ $organisation['ville_commune'] }}
            @endif
            @if(!empty($organisation['boite_postale']))
                , BP : {{ $organisation['boite_postale'] }}
            @endif
        </p>

        <p class="mb-20">

            @if(isset($organisation_membres) && count($organisation_membres) > 0)
                @foreach($organisation_membres->take(3) as $index => $membre)
                    <strong><u>{{ $membre['fonction'] }}</u></strong> :
                    {{ $membre['nom_complet'] }}@if($index < min(2, count($organisation_membres) - 1))
                    ;<br />@endif
                @endforeach
            @else
                <span style="text-transform:capitalize"><strong><u>{{ $organisation['president_fonction'] ?? 'Coordinateur Général' }}
                        </u></strong></span>:
                {{ $organisation['president_nom'] ?? '[Nom du Président]' }}
            @endif
        </p>

        <div style="page-break-before: always;"></div>

        <p class="mb-20">
            <strong>Pièces annexées à la déclaration et autres prescriptions :</strong>
        </p>

        <div>
            <strong>1- Pièces annexées :</strong>
        </div>
        <ul style="margin-left: 30px; margin-bottom:6px; font-size:12pt; line-height: normal;">
            <li>Statuts ;</li>
            <li>Procès-verbal ;</li>
            <li>La liste de tous les membres du comité directeur ;</li>
            <li>La demande adressée au Ministre de l'Intérieur ;</li>
            <li>Le reçu de 10.000 frs CFA délivré par la Direction du Journal Officiel.</li>
        </ul>

        <p class="mb-4">
            <strong>2- Prescriptions :</strong>
        </p>

        <div style="text-align: justify; font-size:12pt; line-height: normal;">
            Toutes modifications apportées aux statuts de l'association et tous les changements survenus dans son
            administration ou sa direction devront être déclarés dans un délai d'un mois et mentionnés en outre dans
            le registre spécial tenu aussi bien au secrétariat de la préfecture qu'au siège de l'association,
            conformément aux dispositions de l'article 11 de la loi citée ci-dessus. Ce registre devra être présenté
            sur leur demande aux autorités administratives et judiciaires.

            <br />
            Sous peine de nullité de l'association dont la dissolution peut être à tout moment prononcée par décret
            pris par l'autorité compétente conformément aux dispositions de l'ordonnance numéro 17/PR du 17 avril 1965,
            les membres de ladite association doivent strictement observer les dispositions des articles 4 et 5 de cette
            même ordonnance qui stipule que :
        </div>

        <div style="text-align: justify; font-size:12pt; line-height: normal; margin-top:4pt">
            <strong>Premièrement :</strong> « Toute association fondée sur une cause en vue d'un objet illicite
            contrairement
            aux lois, aux bonnes mœurs ou qui aurait pour but de porter atteinte à l'intégrité du territoire national et à
            la forme républicaine du Gouvernement, ou qui serait de nature à compromettre la sécurité publique, à provoquer
            la haine entre groupes ethniques, à occasionner des troubles publics, à jeter le discrédit sur les institutions
            politiques ou leur fonctionnement, à inciter les citoyens à enfreindre les lois et à nuire à l'intérêt général
            est nulle et de nul effet ».
        </div>

        <div style="text-align: justify; font-size:12pt; line-height: normal; margin-top:4pt">
            <strong>Deuxièmement :</strong> « Sous peine de nullité de l'association, les membres chargés de son
            administration
            ou de sa direction doivent être majeurs, jouir de leurs droits civiques et ne pas avoir encouru de condamnation
            à
            une peine criminelle ou correctionnelle, à l'exception toutefois des condamnations pour délit d'imprudence hors
            le
            cas de délit de fuite ».
        </div>
    </div>

    <div class="signature-block" style="margin-top:1px; padding-top: 1px;">
        @include('documents.components.signature')
    </div>

@endsection