<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdherentAnomalie extends Model
{
    use HasFactory;

    /**
     * Nom de la table
     */
    protected $table = 'adherent_anomalies';

    /**
     * Champs mass assignable
     */
    protected $fillable = [
        'adherent_id',
        'organisation_id',
        'ligne_import',
        'type_anomalie',
        'champ_concerne',
        'message_anomalie',
        'description',
        'detectee_le',
        'valeur_erronee',
        'valeur_incorrecte',
        'impact_metier',
        'priorite',
        'statut',
        'valeur_corrigee',
        'commentaire_correction',
        'corrige_par',
        'date_correction'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'detectee_le' => 'datetime',
        'date_correction' => 'datetime',
        'valeur_erronee' => 'array',
        'valeur_incorrecte' => 'array',
        'priorite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Constantes pour les types d'anomalies
     */
    const TYPE_CRITIQUE = 'critique';
    const TYPE_MAJEURE = 'majeure';
    const TYPE_MINEURE = 'mineure';

    /**
     * Constantes pour les statuts
     */
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_DETECTEE = 'detectee';
    const STATUT_RESOLU = 'resolu';
    const STATUT_IGNORE = 'ignore';

    /**
     * Mapping des codes d'anomalies vers les champs concernés
     */
    const ANOMALIE_CHAMP_MAPPING = [
        'nip_absent' => 'nip',
        'nip_invalide' => 'nip',
        'nip_doublon_fichier' => 'nip',
        'nip_doublon_organisation' => 'nip',
        'nip_non_trouve_database' => 'nip',
        'donnees_incoherentes_database' => 'donnees_personnelles',
        'age_mineur' => 'date_naissance',
        'age_suspect' => 'date_naissance',
        'double_appartenance_parti' => 'organisation',
        'profession_exclue_parti' => 'profession',
        'profession_manquante' => 'profession',
        'telephone_invalide' => 'telephone',
        'email_invalide' => 'email'
    ];

    /**
     * Relation avec l'adhérent
     */
    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class);
    }

    /**
     * Relation avec l'organisation
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Relation avec l'utilisateur qui a corrigé
     */
    public function corrigePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrige_par');
    }

    /**
     * Scopes
     */
    public function scopeCritiques($query)
    {
        return $query->where('type_anomalie', self::TYPE_CRITIQUE);
    }

    public function scopeMajeures($query)
    {
        return $query->where('type_anomalie', self::TYPE_MAJEURE);
    }

    public function scopeMineures($query)
    {
        return $query->where('type_anomalie', self::TYPE_MINEURE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopeDetectees($query)
    {
        return $query->where('statut', self::STATUT_DETECTEE);
    }

    public function scopeResolues($query)
    {
        return $query->where('statut', self::STATUT_RESOLU);
    }

    public function scopeNonResolues($query)
    {
        return $query->whereIn('statut', [self::STATUT_EN_ATTENTE, self::STATUT_DETECTEE]);
    }

    /**
     * Méthode statique pour créer une anomalie depuis les données du modèle Adherent
     */
    public static function createFromAdherentData(Adherent $adherent, array $anomalieData, int $ligneImport = 0): self
    {
        // Mapper le code d'anomalie vers le champ concerné
        $champConcerne = self::ANOMALIE_CHAMP_MAPPING[$anomalieData['code']] ?? 'general';
        
        // Déterminer la priorité selon le type
        switch($anomalieData['type']) {
            case 'critique':
                $priorite = 1;
                break;
            case 'majeure':
                $priorite = 2;
                break;
            case 'mineure':
                $priorite = 3;
                break;
            default:
                $priorite = 3;
        }

        return self::create([
            'adherent_id' => $adherent->id,
            'organisation_id' => $adherent->organisation_id,
            'ligne_import' => $ligneImport,
            'type_anomalie' => $anomalieData['type'],
            'champ_concerne' => $champConcerne,
            'message_anomalie' => $anomalieData['message'],
            'description' => self::formatDescription($anomalieData),
            'detectee_le' => now(),
            'valeur_erronee' => $anomalieData['details'] ?? null,
            'valeur_incorrecte' => $anomalieData['details'] ?? null,
            'impact_metier' => self::determineImpactMetier($anomalieData['code'], $anomalieData['type']),
            'priorite' => $priorite,
            'statut' => self::STATUT_DETECTEE
        ]);
    }

    /**
     * Formater la description de l'anomalie
     */
    private static function formatDescription(array $anomalieData): string
    {
        $description = $anomalieData['message'];
        
        if (!empty($anomalieData['action_requise'])) {
            $description .= "\n\nAction requise: " . $anomalieData['action_requise'];
        }
        
        if (!empty($anomalieData['date_detection'])) {
            $description .= "\n\nDétectée le: " . $anomalieData['date_detection'];
        }
        
        return $description;
    }

    /**
     * ✅ CORRECTION CRITIQUE : Déterminer l'impact métier selon le code d'anomalie
     * REMPLACE $this-> par self:: pour compatibilité méthode statique
     */
    private static function determineImpactMetier(string $code, string $type): string
    {
        $impacts = [
            'nip_non_trouve_database' => 'Risque de fraude - NIP non authentifié',
            'donnees_incoherentes_database' => 'Incohérence avec base officielle',
            'double_appartenance_parti' => 'Violation règles politiques',
            'profession_exclue_parti' => 'Incompatibilité réglementaire',
            'age_mineur' => 'Non-respect âge minimum',
            'nip_doublon_organisation' => 'Doublon inter-organisations',
            'nip_invalide' => 'Format NIP non conforme',
            'nip_absent' => 'Identification impossible'
        ];

        // ✅ CORRECTION : Remplace $this-> par self::
        return $impacts[$code] ?? self::getDefaultImpactByType($type);
    }

    /**
     * ✅ CORRECTION CRITIQUE : Helper pour l'impact par défaut selon le type
     * MÉTHODE DÉSORMAIS STATIQUE pour éviter erreur "Using $this when not in object context"
     */
    private static function getDefaultImpactByType(string $type): string
    {
        switch($type) {
            case 'critique':
                return 'Impact critique sur la validité';
            case 'majeure':
                return 'Impact important sur la cohérence';
            case 'mineure':
                return 'Impact mineur sur la qualité';
            default:
                return 'Impact à évaluer';
        }
    }

    /**
     * Méthode pour marquer une anomalie comme corrigée
     */
    public function marquerCommeCorrigee(string $valeurCorrigee, string $commentaire = '', ?int $userId = null): bool
    {
        return $this->update([
            'statut' => self::STATUT_RESOLU,
            'valeur_corrigee' => $valeurCorrigee,
            'commentaire_correction' => $commentaire,
            'corrige_par' => $userId ?? auth()->id(),
            'date_correction' => now()
        ]);
    }

    /**
     * Méthode pour ignorer une anomalie
     */
    public function ignorer(string $motif = '', ?int $userId = null): bool
    {
        return $this->update([
            'statut' => self::STATUT_IGNORE,
            'commentaire_correction' => "Anomalie ignorée: " . $motif,
            'corrige_par' => $userId ?? auth()->id(),
            'date_correction' => now()
        ]);
    }

    /**
     * Obtenir le libellé du type d'anomalie
     */
    public function getTypeLibelle(): string
    {
        switch($this->type_anomalie) {
            case self::TYPE_CRITIQUE:
                return 'Critique';
            case self::TYPE_MAJEURE:
                return 'Majeure';
            case self::TYPE_MINEURE:
                return 'Mineure';
            default:
                return 'Inconnue';
        }
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelle(): string
    {
        switch($this->statut) {
            case self::STATUT_EN_ATTENTE:
                return 'En attente';
            case self::STATUT_DETECTEE:
                return 'Détectée';
            case self::STATUT_RESOLU:
                return 'Résolue';
            case self::STATUT_IGNORE:
                return 'Ignorée';
            default:
                return 'Inconnu';
        }
    }

    /**
     * Obtenir la classe CSS selon le type
     */
    public function getCssClass(): string
    {
        switch($this->type_anomalie) {
            case self::TYPE_CRITIQUE:
                return 'text-danger';
            case self::TYPE_MAJEURE:
                return 'text-warning';
            case self::TYPE_MINEURE:
                return 'text-info';
            default:
                return 'text-secondary';
        }
    }

    /**
     * Obtenir l'icône selon le type
     */
    public function getIconClass(): string
    {
        switch($this->type_anomalie) {
            case self::TYPE_CRITIQUE:
                return 'fas fa-exclamation-triangle';
            case self::TYPE_MAJEURE:
                return 'fas fa-exclamation-circle';
            case self::TYPE_MINEURE:
                return 'fas fa-info-circle';
            default:
                return 'fas fa-question-circle';
        }
    }

    /**
     * Statistiques des anomalies
     */
    public static function getStatistiques(): array
    {
        return [
            'total' => self::count(),
            'critiques' => self::critiques()->count(),
            'majeures' => self::majeures()->count(),
            'mineures' => self::mineures()->count(),
            'en_attente' => self::enAttente()->count(),
            'detectees' => self::detectees()->count(),
            'resolues' => self::resolues()->count(),
            'non_resolues' => self::nonResolues()->count(),
            'par_organisation' => self::select('organisation_id')
                ->selectRaw('COUNT(*) as total')
                ->with('organisation:id,nom')
                ->groupBy('organisation_id')
                ->get()
                ->pluck('total', 'organisation.nom')
                ->toArray()
        ];
    }

    /**
     * ✅ CORRECTION CRITIQUE : Méthode pour traiter en lot les anomalies d'un adhérent
     * RÉSOUT l'erreur "Using $this when not in object context"
     */
    public static function createBulkFromAdherent(Adherent $adherent, int $ligneImport = 0): array
    {
        $anomaliesCreated = [];
        
        if (!$adherent->has_anomalies || !$adherent->anomalies_data) {
            return $anomaliesCreated;
        }

        try {
            foreach ($adherent->anomalies_data as $anomalieData) {
                $anomalie = self::createFromAdherentData($adherent, $anomalieData, $ligneImport);
                $anomaliesCreated[] = $anomalie;
            }
            
            \Log::info('✅ Anomalies créées dans adherent_anomalies', [
                'adherent_id' => $adherent->id,
                'nip' => $adherent->nip,
                'anomalies_count' => count($anomaliesCreated),
                'anomalies_codes' => array_column($adherent->anomalies_data, 'code')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Erreur création anomalies dans adherent_anomalies', [
                'adherent_id' => $adherent->id,
                'nip' => $adherent->nip,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $anomaliesCreated;
    }
}