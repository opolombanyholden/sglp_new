{{-- 
    FICHIER À CRÉER : resources/views/operator/dossiers/rapport-minimal-pdf.blade.php
    Vue PDF ultra-légère pour gros volumes
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Anomalies - {{ $organisation->nom ?? 'Organisation' }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            margin: 15px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #009e3f; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .header h1 { 
            color: #009e3f; 
            font-size: 16px; 
            margin: 0 0 5px 0; 
        }
        .stats-grid { 
            display: table; 
            width: 100%; 
            margin: 20px 0; 
        }
        .stat-item { 
            display: table-cell; 
            width: 25%; 
            text-align: center; 
            padding: 10px; 
            border: 1px solid #ddd; 
            background: #f8f9fa;
        }
        .stat-number { 
            font-size: 18px; 
            font-weight: bold; 
            color: #009e3f; 
            display: block; 
        }
        .stat-label { 
            font-size: 9px; 
            color: #666; 
            margin-top: 3px; 
        }
        .top-anomalies { 
            margin: 20px 0; 
        }
        .anomalie-item { 
            padding: 8px; 
            border-bottom: 1px solid #eee; 
            font-size: 9px; 
        }
        .nip { 
            font-weight: bold; 
            color: #dc3545; 
        }
        .note { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            padding: 10px; 
            margin: 20px 0; 
            font-size: 9px; 
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 8px; 
            color: #666; 
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>RAPPORT D'ANOMALIES - VERSION ALLÉGÉE v2</h1>
        <div>{{ $organisation->nom ?? 'Organisation' }} | {{ $metadata['genere_le'] }}</div>
    </div>

    {{-- STATISTIQUES --}}
    <div class="stats-grid">
        <div class="stat-item">
            <span class="stat-number">{{ $stats['total'] }}</span>
            <div class="stat-label">Total Adhérents</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $stats['valides'] }}</span>
            <div class="stat-label">Valides</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $stats['avec_anomalies'] }}</span>
            <div class="stat-label">Avec Anomalies</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $stats['taux_validite'] }}%</span>
            <div class="stat-label">Taux Validité</div>
        </div>
    </div>

    {{-- RÉPARTITION DES ANOMALIES --}}
    <div style="margin: 20px 0;">
        <h3 style="color: #009e3f; font-size: 12px; margin-bottom: 10px;">Répartition des Anomalies</h3>
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 33%; text-align: center; padding: 8px; background: #dc3545; color: white;">
                <strong>{{ $stats['anomalies_critiques'] }}</strong><br>
                <small>Critiques</small>
            </div>
            <div style="display: table-cell; width: 33%; text-align: center; padding: 8px; background: #ffc107; color: #333;">
                <strong>{{ $stats['anomalies_majeures'] }}</strong><br>
                <small>Majeures</small>
            </div>
            <div style="display: table-cell; width: 33%; text-align: center; padding: 8px; background: #17a2b8; color: white;">
                <strong>{{ $stats['anomalies_mineures'] }}</strong><br>
                <small>Mineures</small>
            </div>
        </div>
    </div>

    {{-- TOP ANOMALIES CRITIQUES --}}
    @if(isset($anomaliesCritiques) && $anomaliesCritiques->count() > 0)
    <div class="top-anomalies">
        <h3 style="color: #dc3545; font-size: 12px; margin-bottom: 10px;">🚨 Top {{ $anomaliesCritiques->count() }} Anomalies Critiques</h3>
        @foreach($anomaliesCritiques as $anomalie)
        <div class="anomalie-item">
            <span class="nip">{{ $anomalie->nip }}</span> - 
            {{ $anomalie->nom }} {{ $anomalie->prenom }} : 
            <em>{{ $anomalie->message_anomalie }}</em>
        </div>
        @endforeach
    </div>
    @elseif(isset($topAnomalies) && $topAnomalies->count() > 0)
    <div class="top-anomalies">
        <h3 style="color: #dc3545; font-size: 12px; margin-bottom: 10px;">🚨 Top {{ $topAnomalies->count() }} Anomalies Critiques</h3>
        @foreach($topAnomalies as $anomalie)
        <div class="anomalie-item">
            <span class="nip">{{ $anomalie->nip }}</span> - 
            {{ $anomalie->nom }} {{ $anomalie->prenom }} : 
            <em>{{ $anomalie->message_anomalie }}</em>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 20px; color: #28a745;">
        <h3>✅ Aucune anomalie critique détectée</h3>
        <p>Les anomalies présentes sont de niveau majeur ou mineur.</p>
    </div>
    @endif

    {{-- NOTE EXPLICATIVE --}}
    <div class="note">
        <strong>📋 Important :</strong> {{ $metadata['note'] }}
        <br><strong>Total anomalies système :</strong> {{ number_format($metadata['total_anomalies']) }}
    </div>

    {{-- FOOTER --}}
    <div class="footer text-black">
        <p>
            <table style="float: left; width:100%;">
                <tr>
                    <td style="width:60%;"></td>
                
                    <td style="width:40%; font-weight: bold; text-align: center; font-size:14px;">
                        Le Directeur Général des Elections<br/>
                        et des Libertés Publiques<br/><br/><br/><br/><br/><br/>

                        Dieudonné YAYA
                    </td>
                </tr>
            </table>
        </p>
        <br/><br/><br/><br/><br/><br/><br/>

        <!-- Pied de page officiel -->
        <div class="footer-officiel">
            <div style="font-weight: bold; color: #003f7f;">
                Rapport généré automatiquement le {{ $stats['date_generation'] }} par le Système de Gestion des Libertés Publiques.
            </div>
        
            <div class="footer-ministeriel">
                <strong>MINISTÈRE DE L'INTÉRIEUR, DE LA SÉCURITÉ ET DE LA DÉCENTRALISATION</strong><br>
                119, RUE Jean Baptiste NDENDE, (Avenue de Cointet BP 2110 Libreville, Gabon)<br>
                <em>Ce document contient des informations confidentielles. Sa diffusion est strictement réservée aux personnes autorisées.</em>
            </div>
        </div>
    </div>
</body>
</html>