@extends('documents.layouts.official')

@section('content')

<div class="document-title">
    RÉCÉPISSÉ PROVISOIRE DE DÉCLARATION
</div>

<div class="document-number">
    N° {{ $document['numero_document'] }}
</div>

<div class="content">
    <p class="mb-20">
        Le Directeur Général de la Sécurité Intérieure, Direction des Investigations,
    </p>
    
    <p class="text-center bold mb-20" style="font-size: 12pt;">
        CERTIFIE
    </p>
    
    <p class="mb-20">
        Avoir reçu en date du <strong>{{ $document['date_generation'] }}</strong>, 
        la déclaration de création d'une association régie par la loi n° 016/2025 du 15 octobre 2025 
        relative aux associations, dénommée :
    </p>
    
    <div class="info-box">
        <h3>INFORMATIONS SUR L'ASSOCIATION</h3>
        
        <table class="info-table">
            <tr>
                <td>Dénomination</td>
                <td><strong>{{ $organisation['nom'] }}</strong></td>
            </tr>
            @if(!empty($organisation['sigle']))
            <tr>
                <td>Sigle</td>
                <td>{{ $organisation['sigle'] }}</td>
            </tr>
            @endif
            <tr>
                <td>Siège social</td>
                <td>{{ $organisation['siege_social'] }}</td>
            </tr>
            <tr>
                <td>Province</td>
                <td>{{ $organisation['province'] }}</td>
            </tr>
            <tr>
                <td>Téléphone</td>
                <td>{{ $organisation['telephone'] ?? 'Non renseigné' }}</td>
            </tr>
            @if(!empty($organisation['email']))
            <tr>
                <td>Email</td>
                <td>{{ $organisation['email'] }}</td>
            </tr>
            @endif
            <tr>
                <td>Date de création</td>
                <td>{{ $organisation['date_creation'] }}</td>
            </tr>
        </table>
    </div>
    
    @if(isset($dossier))
    <div class="info-box">
        <h3>DOSSIER DE DÉCLARATION</h3>
        <table class="info-table">
            <tr>
                <td>Numéro de dossier</td>
                <td><strong>{{ $dossier['numero_dossier'] }}</strong></td>
            </tr>
            <tr>
                <td>Date de soumission</td>
                <td>{{ $dossier['date_soumission'] }}</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>{{ $dossier['statut'] }}</td>
            </tr>
        </table>
    </div>
    @endif
    
    <p class="mt-30 mb-20">
        Le présent récépissé provisoire est délivré en attendant la délivrance 
        du récépissé définitif, conformément aux dispositions de la loi n° 016/2025 
        du 15 octobre 2025 relative aux associations.
    </p>
    
    <p class="mb-20">
        <strong>Validité :</strong> Ce récépissé provisoire est valable pour une durée 
        de <strong>90 jours</strong> à compter de sa date d'émission, soit jusqu'au 
        <strong>{{ now()->addDays(90)->format('d/m/Y') }}</strong>.
    </p>
    
    <div class="warning-box">
        <p><strong>⚠️ IMPORTANT :</strong></p>
        <p style="margin-top: 8px;">
            Ce récépissé ne vaut pas autorisation définitive de fonctionnement. 
            L'association doit attendre le récépissé définitif pour exercer pleinement ses activités 
            et bénéficier de la personnalité morale.
        </p>
    </div>
    
    <p class="mt-30 mb-10">
        Le dossier sera examiné par les services compétents. Toute pièce manquante ou irrégularité 
        sera notifiée aux responsables de l'association.
    </p>
</div>

<div class="signature-block">
    <p>Fait à Libreville, le {{ now()->format('d/m/Y') }}</p>
    
    @include('documents.components.signature')
</div>

@endsection
