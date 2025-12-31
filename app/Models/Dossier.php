<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Adherent;

class Dossier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organisation_id',
        'type_operation',
        'numero_dossier',
        'statut',
        'date_soumission',
        'submitted_at',
        'date_traitement',
        'validated_at',
        'motif_rejet',
        'current_step_id',
        'is_active',
        'metadata',
        'donnees_supplementaires',
        // Champs d'assignation
        'assigned_to',
        // Champs de priorité FIFO
        'priorite_niveau',
        'priorite_urgente',
        'ordre_traitement',
        'priorite_justification',
        'priorite_assignee_par',
        'priorite_assignee_at',
        'instructions_agent',
        // Champs de verrouillage
        'is_locked',
        'locked_at',
        'locked_by',
        // Autres
        'has_anomalies_majeures',
        // Champs de versioning
        'parent_dossier_id',
        'version',
        'is_current_version',
        'champs_modifies',
        'donnees_avant_modification'
    ];

    protected $casts = [
        'date_soumission' => 'datetime',
        'date_traitement' => 'datetime',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
        'priorite_assignee_at' => 'datetime',
        'locked_at' => 'datetime',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'is_current_version' => 'boolean',
        'priorite_urgente' => 'boolean',
        'has_anomalies_majeures' => 'boolean',
        'metadata' => 'array',
        'donnees_supplementaires' => 'array',
        'champs_modifies' => 'array',
        'donnees_avant_modification' => 'array'
    ];

    // Constantes pour les types d'opération
    const TYPE_CREATION = 'creation';
    const TYPE_MODIFICATION = 'modification';
    const TYPE_CESSATION = 'cessation';
    const TYPE_DECLARATION = 'declaration';
    const TYPE_FUSION = 'fusion';
    const TYPE_ABSORPTION = 'absorption';
    const TYPE_AJOUT_ADHERENT = 'ajout_adherent';
    const TYPE_RETRAIT_ADHERENT = 'retrait_adherent';
    const TYPE_DECLARATION_ACTIVITE = 'declaration_activite';
    const TYPE_CHANGEMENT_STATUTAIRE = 'changement_statutaire';

    // Constantes pour les statuts
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_SOUMIS = 'soumis';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_ACCEPTE = 'accepte';
    const STATUT_REJETE = 'rejete';
    const STATUT_ARCHIVE = 'archive';
    const STATUT_ANNULE = 'annule';

    /**
     * Boot method pour générer automatiquement le numéro de dossier
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dossier) {
            if (empty($dossier->numero_dossier)) {
                $dossier->numero_dossier = self::generateNumeroDossier($dossier->type_operation);
            }
        });
    }

    /**
     * Générer un numéro de dossier unique
     */
    public static function generateNumeroDossier($typeOperation): string
    {
        // Version compatible PHP 7.4
        switch ($typeOperation) {
            case self::TYPE_CREATION:
                $prefix = 'CRE';
                break;
            case self::TYPE_MODIFICATION:
                $prefix = 'MOD';
                break;
            case self::TYPE_CESSATION:
                $prefix = 'CES';
                break;
            case self::TYPE_DECLARATION:
                $prefix = 'DEC';
                break;
            case self::TYPE_FUSION:
                $prefix = 'FUS';
                break;
            case self::TYPE_ABSORPTION:
                $prefix = 'ABS';
                break;
            case self::TYPE_AJOUT_ADHERENT:
                $prefix = 'AJA';
                break;
            case self::TYPE_RETRAIT_ADHERENT:
                $prefix = 'RET';
                break;
            case self::TYPE_DECLARATION_ACTIVITE:
                $prefix = 'DAC';
                break;
            case self::TYPE_CHANGEMENT_STATUTAIRE:
                $prefix = 'CST';
                break;
            default:
                $prefix = 'DOS';
                break;
        }

        $year = date('Y');
        $lastDossier = self::where('numero_dossier', 'like', $prefix . '-' . $year . '-%')
            ->orderBy('numero_dossier', 'desc')
            ->first();

        if ($lastDossier) {
            $lastNumber = intval(substr($lastDossier->numero_dossier, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $newNumber);
    }

    /**
     * Relations
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DossierValidation::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(DossierOperation::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DossierComment::class);
    }

    public function lock(): HasOne
    {
        return $this->hasOne(DossierLock::class);
    }

    /**
     * =========================================
     * RELATIONS DE VERSIONING
     * =========================================
     */

    /**
     * Relation vers le dossier parent (version précédente)
     */
    public function parentDossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class, 'parent_dossier_id');
    }

    /**
     * Relation vers les versions enfants (versions suivantes)
     */
    public function childVersions(): HasMany
    {
        return $this->hasMany(Dossier::class, 'parent_dossier_id')
            ->orderBy('version', 'desc');
    }

    /**
     * Obtenir le dossier racine (première version)
     */
    public function getRootDossier(): ?Dossier
    {
        if (!$this->parent_dossier_id) {
            return $this;
        }

        $current = $this;
        while ($current->parent_dossier_id) {
            $current = $current->parentDossier;
        }
        return $current;
    }

    /**
     * Obtenir toutes les versions d'un dossier (y compris lui-même)
     */
    public function getAllVersions()
    {
        $rootDossier = $this->getRootDossier();

        return Dossier::where(function ($query) use ($rootDossier) {
            $query->where('id', $rootDossier->id)
                ->orWhere('parent_dossier_id', $rootDossier->id);
        })
            ->withTrashed()
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * =========================================
     * MÉTHODES DE VERSIONING
     * =========================================
     */

    /**
     * Dupliquer le dossier pour créer une nouvelle version
     * 
     * @param string $typeOperation Le type d'opération pour le nouveau dossier
     * @param array|null $champsModifies Les champs sélectionnés pour modification
     * @return Dossier La nouvelle version du dossier
     */
    public function duplicate(string $typeOperation, ?array $champsModifies = null): Dossier
    {
        // Marquer cette version comme non-courante
        $this->update(['is_current_version' => false]);

        // Créer le snapshot des données avant modification
        $snapshot = $this->createSnapshot();

        // Créer la nouvelle version
        $newDossier = new Dossier();
        $newDossier->organisation_id = $this->organisation_id;
        $newDossier->parent_dossier_id = $this->id;
        $newDossier->version = $this->version + 1;
        $newDossier->is_current_version = true;
        $newDossier->type_operation = $typeOperation;
        $newDossier->statut = Dossier::STATUT_BROUILLON;
        $newDossier->numero_dossier = Dossier::generateNumeroDossier($typeOperation);
        $newDossier->champs_modifies = $champsModifies;
        $newDossier->donnees_avant_modification = $snapshot;
        $newDossier->is_active = true;
        $newDossier->save();

        return $newDossier;
    }

    /**
     * Créer un snapshot complet des données actuelles
     * 
     * @return array
     */
    public function createSnapshot(): array
    {
        $this->load(['organisation.adherents', 'organisation.fondateurs', 'organisation.membresBureau']);

        return [
            'dossier' => [
                'id' => $this->id,
                'numero_dossier' => $this->numero_dossier,
                'type_operation' => $this->type_operation,
                'statut' => $this->statut,
                'version' => $this->version,
                'donnees_supplementaires' => $this->donnees_supplementaires,
            ],
            'organisation' => $this->organisation ? [
                'nom' => $this->organisation->nom,
                'sigle' => $this->organisation->sigle,
                'objet' => $this->organisation->objet,
                'devise' => $this->organisation->devise,
                'siege_social' => $this->organisation->siege_social,
                'province' => $this->organisation->province,
                'commune' => $this->organisation->commune,
                'quartier' => $this->organisation->quartier,
                'telephone' => $this->organisation->telephone,
                'email' => $this->organisation->email,
                'statut' => $this->organisation->statut,
                'numero_recepisse' => $this->organisation->numero_recepisse,
            ] : null,
            'adherents' => $this->organisation ? $this->organisation->adherents->map(function ($adherent) {
                return [
                    'id' => $adherent->id,
                    'nom' => $adherent->nom,
                    'prenom' => $adherent->prenom,
                    'nationalite' => $adherent->nationalite ?? null,
                    'is_active' => $adherent->is_active,
                ];
            })->toArray() : [],
            'fondateurs' => $this->organisation ? $this->organisation->fondateurs->map(function ($fondateur) {
                return [
                    'id' => $fondateur->id,
                    'nom' => $fondateur->nom,
                    'prenom' => $fondateur->prenom,
                    'fonction' => $fondateur->fonction ?? null,
                ];
            })->toArray() : [],
            'membres_bureau' => $this->organisation ? $this->organisation->membresBureau->map(function ($membre) {
                return [
                    'id' => $membre->id,
                    'nom' => $membre->nom,
                    'prenom' => $membre->prenom,
                    'fonction' => $membre->fonction ?? null,
                ];
            })->toArray() : [],
            'date_snapshot' => now()->toDateTimeString(),
        ];
    }

    /**
     * Vérifier si ce dossier est la version courante
     */
    public function isCurrentVersion(): bool
    {
        return (bool) $this->is_current_version;
    }

    /**
     * Obtenir la version courante pour cette organisation
     */
    public static function getCurrentVersionForOrganisation(int $organisationId): ?Dossier
    {
        return self::where('organisation_id', $organisationId)
            ->where('is_current_version', true)
            ->latest()
            ->first();
    }

    /**
     * Agent assigné au dossier
     */
    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Utilisateur qui a verrouillé le dossier
     */
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Utilisateur qui a assigné la priorité
     */
    public function prioriteAssigneePar()
    {
        return $this->belongsTo(User::class, 'priorite_assignee_par');
    }

    /**
     * ✅ CORRECTION PRINCIPALE - Relation avec les adhérents via organisation
     * 
     * LOGIQUE : Un dossier appartient à une organisation, 
     * et les adhérents appartiennent également à cette organisation.
     * Donc on accède aux adhérents VIA l'organisation.
     */
    public function adherents()
    {
        return $this->hasManyThrough(
            Adherent::class,           // Modèle final (Adherent)
            Organisation::class,       // Modèle intermédiaire (Organisation)
            'id',                      // Clé étrangère sur table organisations (pour relation dossiers->organisations)
            'organisation_id',         // Clé étrangère sur table adherents (pour relation organisations->adherents)  
            'organisation_id',         // Clé locale sur table dossiers
            'id'                       // Clé locale sur table organisations
        );
    }

    /**
     * ✅ MÉTHODE ALTERNATIVE - Accès direct aux adhérents de l'organisation
     * 
     * Plus simple et plus claire que hasManyThrough
     */
    public function getAdherentsAttribute()
    {
        return $this->organisation ? $this->organisation->adherents : collect();
    }

    /**
     * ✅ MÉTHODE UTILITAIRE - Obtenir les adhérents avec filtres
     */
    public function getAdherentsWithFilters($actifs = null, $withAnomalies = null)
    {
        if (!$this->organisation) {
            return collect();
        }

        $query = $this->organisation->adherents();

        if ($actifs !== null) {
            $query->where('is_active', $actifs);
        }

        if ($withAnomalies !== null) {
            $query->where('has_anomalies', $withAnomalies);
        }

        return $query->get();
    }

    /**
     * ✅ MÉTHODE STATISTIQUES - Compteurs adhérents
     */
    public function getAdherentsStats(): array
    {
        if (!$this->organisation) {
            return [
                'total' => 0,
                'actifs' => 0,
                'inactifs' => 0,
                'fondateurs' => 0,
                'avec_anomalies' => 0,
                'sans_anomalies' => 0
            ];
        }

        $adherents = $this->organisation->adherents();

        return [
            'total' => $adherents->count(),
            'actifs' => $adherents->where('is_active', true)->count(),
            'inactifs' => $adherents->where('is_active', false)->count(),
            'fondateurs' => $adherents->where('is_fondateur', true)->count(),
            'avec_anomalies' => $adherents->where('has_anomalies', true)->count(),
            'sans_anomalies' => $adherents->where('has_anomalies', false)->count()
        ];
    }

    /**
     * Scopes
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeSoumis($query)
    {
        return $query->where('statut', self::STATUT_SOUMIS);
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    public function scopeNonTraites($query)
    {
        return $query->whereIn('statut', [self::STATUT_SOUMIS, self::STATUT_EN_COURS]);
    }

    /**
     * Scope pour les dossiers annulés
     */
    public function scopeAnnules($query)
    {
        return $query->where('statut', self::STATUT_ANNULE);
    }

    /**
     * Scope pour exclure les dossiers annulés
     */
    public function scopeNonAnnules($query)
    {
        return $query->where('statut', '!=', self::STATUT_ANNULE);
    }

    /**
     * Scope pour les versions courantes uniquement
     */
    public function scopeCurrentVersions($query)
    {
        return $query->where('is_current_version', true);
    }

    /**
     * Scope pour les anciennes versions (historique)
     */
    public function scopeOldVersions($query)
    {
        return $query->where('is_current_version', false);
    }

    /**
     * Vérifier si le dossier est verrouillé
     */
    public function isLocked(): bool
    {
        return $this->lock()->where('is_active', true)->exists();
    }

    /**
     * Vérifier si le dossier est verrouillé par un utilisateur spécifique
     */
    public function isLockedBy($userId): bool
    {
        return $this->lock()
            ->where('is_active', true)
            ->where('locked_by', $userId)
            ->exists();
    }

    /**
     * Obtenir l'utilisateur qui a verrouillé le dossier
     */
    public function getLockedByUser()
    {
        $lock = $this->lock()->where('is_active', true)->first();
        return $lock ? $lock->user : null;
    }

    /**
     * Vérifier si le dossier peut être modifié
     */
    public function canBeModified(): bool
    {
        return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_REJETE]);
    }

    /**
     * Vérifier si le dossier peut être édité (formulaire d'édition)
     * Seuls les dossiers en brouillon peuvent être édités
     */
    public function canBeEdited(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si le dossier peut être annulé
     * Un dossier peut être annulé s'il n'est pas déjà accepté ou archivé
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->statut, [self::STATUT_ACCEPTE, self::STATUT_ARCHIVE, self::STATUT_ANNULE]);
    }

    /**
     * Vérifier si le dossier peut être soumis
     */
    public function canBeSubmitted(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si tous les documents obligatoires sont fournis
     */
    public function hasAllRequiredDocuments(): bool
    {
        $requiredTypes = DocumentType::where('type_organisation', $this->organisation->type)
            ->where('is_active', true)
            ->where('is_obligatoire', true)
            ->pluck('id');

        $providedTypes = $this->documents()->pluck('document_type_id');

        return $requiredTypes->diff($providedTypes)->isEmpty();
    }

    /**
     * Obtenir les documents manquants
     */
    public function getMissingDocuments()
    {
        $requiredTypes = DocumentType::where('type_organisation', $this->organisation->type)
            ->where('is_active', true)
            ->where('is_obligatoire', true)
            ->pluck('id');

        $providedTypes = $this->documents()->pluck('document_type_id');

        $missingIds = $requiredTypes->diff($providedTypes);

        return DocumentType::whereIn('id', $missingIds)->get();
    }

    /**
     * Obtenir la prochaine étape du workflow
     */
    public function getNextStep()
    {
        if (!$this->current_step_id) {
            return WorkflowStep::where('type_organisation', $this->organisation->type)
                ->where('type_operation', $this->type_operation)
                ->where('is_active', true)
                ->orderBy('ordre')
                ->first();
        }

        return WorkflowStep::where('type_organisation', $this->organisation->type)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->where('ordre', '>', $this->currentStep->ordre)
            ->orderBy('ordre')
            ->first();
    }

    /**
     * Obtenir l'étape précédente du workflow (pour retour en cas de rejet)
     */
    public function getPreviousStep()
    {
        if (!$this->current_step_id) {
            return null;
        }

        return WorkflowStep::where('type_organisation', $this->organisation->type)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->where('ordre', '<', $this->currentStep->ordre)
            ->orderBy('ordre', 'desc')
            ->first();
    }

    /**
     * Obtenir le dernier rejet
     */
    public function getLastRejection()
    {
        return $this->validations()
            ->where('decision', 'rejete')
            ->latest()
            ->first();
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getProgressionPercentage(): int
    {
        if (!$this->current_step_id) {
            return 0;
        }

        $totalSteps = WorkflowStep::where('type_organisation', $this->organisation->type)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->count();

        if ($totalSteps === 0) {
            return 0;
        }

        $currentStepOrder = $this->currentStep->ordre;

        return round(($currentStepOrder / $totalSteps) * 100);
    }

    /**
     * Accesseurs
     */
    public function getTypeOperationLabelAttribute(): string
    {
        $labels = [
            self::TYPE_CREATION => 'Création',
            self::TYPE_MODIFICATION => 'Modification',
            self::TYPE_CESSATION => 'Cessation',
            self::TYPE_DECLARATION => 'Déclaration',
            self::TYPE_FUSION => 'Fusion',
            self::TYPE_ABSORPTION => 'Absorption',
            self::TYPE_AJOUT_ADHERENT => 'Ajout adhérent',
            self::TYPE_RETRAIT_ADHERENT => 'Retrait adhérent',
            self::TYPE_DECLARATION_ACTIVITE => 'Déclaration d\'activité',
            self::TYPE_CHANGEMENT_STATUTAIRE => 'Changement statutaire'
        ];

        return $labels[$this->type_operation] ?? $this->type_operation;
    }

    public function getStatutLabelAttribute(): string
    {
        $labels = [
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_SOUMIS => 'Soumis',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_ACCEPTE => 'Accepté',
            self::STATUT_REJETE => 'Rejeté',
            self::STATUT_ARCHIVE => 'Archivé',
            self::STATUT_ANNULE => 'Annulé'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        $colors = [
            self::STATUT_BROUILLON => 'secondary',
            self::STATUT_SOUMIS => 'info',
            self::STATUT_EN_COURS => 'warning',
            self::STATUT_ACCEPTE => 'success',
            self::STATUT_REJETE => 'danger',
            self::STATUT_ARCHIVE => 'dark',
            self::STATUT_ANNULE => 'danger'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }
}