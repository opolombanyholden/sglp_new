<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODÈLE ORGANISATION TYPE
 * 
 * Gère les types d'organisations avec leurs règles métier,
 * documents requis et templates de documents à délivrer
 * 
 * @property int $id
 * @property string $code
 * @property string $nom
 * @property string|null $description
 * @property string $couleur
 * @property string|null $icone
 * @property bool $is_lucratif
 * @property int $nb_min_fondateurs_majeurs
 * @property int $nb_min_adherents_creation
 * @property string|null $guide_creation
 * @property string|null $texte_legislatif
 * @property string|null $loi_reference
 * @property bool $is_active
 * @property int $ordre
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * Projet : SGLP
 * Compatible : PHP 8.3, Laravel 10+
 */
class OrganisationType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nom de la table
     */
    protected $table = 'organisation_types';

    /**
     * ========================================
     * CONSTANTES - CODES DES TYPES
     * ========================================
     */
    const CODE_ASSOCIATION = 'association';
    const CODE_ONG = 'ong';
    const CODE_PARTI_POLITIQUE = 'parti_politique';
    const CODE_CONFESSION_RELIGIEUSE = 'confession_religieuse';

    /**
     * ========================================
     * FILLABLE - Colonnes assignables en masse
     * ========================================
     */
    protected $fillable = [
        'code',
        'nom',
        'description',
        'couleur',
        'icone',
        'is_lucratif',
        'nb_min_fondateurs_majeurs',
        'nb_min_adherents_creation',
        'guide_creation',
        'texte_legislatif',
        'loi_reference',
        'is_active',
        'ordre',
        'metadata',
    ];

    /**
     * ========================================
     * CASTS - Conversion automatique des types
     * ========================================
     */
    protected $casts = [
        'is_lucratif' => 'boolean',
        'nb_min_fondateurs_majeurs' => 'integer',
        'nb_min_adherents_creation' => 'integer',
        'is_active' => 'boolean',
        'ordre' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * ========================================
     * RELATIONS
     * ========================================
     */

    /**
     * Relation Many-to-Many : Types de documents requis
     * 
     * @return BelongsToMany
     */
    public function documentTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            DocumentType::class,
            'organisation_type_document_type',
            'organisation_type_id',
            'document_type_id'
        )
        ->withPivot([
            'is_obligatoire',
            'ordre',
            'aide_texte',
            'modele_texte',
            'exemple_fichier',
            'regles_validation',
            'metadata'
        ])
        ->withTimestamps()
        ->orderBy('organisation_type_document_type.ordre');
    }

    /**
     * Relation : Documents obligatoires uniquement
     * 
     * @return BelongsToMany
     */
    public function documentTypesObligatoires(): BelongsToMany
    {
        return $this->documentTypes()
            ->wherePivot('is_obligatoire', true);
    }

    /**
     * Relation : Documents facultatifs uniquement
     * 
     * @return BelongsToMany
     */
    public function documentTypesFacultatifs(): BelongsToMany
    {
        return $this->documentTypes()
            ->wherePivot('is_obligatoire', false);
    }

    /**
     * Relation One-to-Many : Templates de documents à délivrer
     * 
     * @return HasMany
     */
    public function documentTemplates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'organisation_type_id')
            ->where('is_active', true)
            ->orderBy('type_document');
    }

    /**
     * Relation One-to-Many : Organisations de ce type
     * 
     * @return HasMany
     */
    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'organisation_type_id');
    }

    /**
     * Relation : Organisations actives uniquement
     * 
     * @return HasMany
     */
    public function organisationsActives(): HasMany
    {
        return $this->organisations()
            ->where('is_active', true)
            ->where('statut', Organisation::STATUT_APPROUVE);
    }

    /**
     * ========================================
     * SCOPES - Requêtes réutilisables
     * ========================================
     */

    /**
     * Scope : Types actifs uniquement
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Types inactifs
     */
    public function scopeInactif($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope : Triés par ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    /**
     * Scope : Types lucratifs
     */
    public function scopeLucratif($query)
    {
        return $query->where('is_lucratif', true);
    }

    /**
     * Scope : Types non lucratifs
     */
    public function scopeNonLucratif($query)
    {
        return $query->where('is_lucratif', false);
    }

    /**
     * Scope : Recherche par code ou nom
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('nom', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * ========================================
     * ACCESSEURS - Attributs calculés
     * ========================================
     */

    /**
     * Obtenir le label du but (lucratif/non lucratif)
     */
    public function getButLabelAttribute(): string
    {
        return $this->is_lucratif ? 'Lucratif' : 'Non lucratif';
    }

    /**
     * Obtenir le nombre d'organisations de ce type
     */
    public function getNbOrganisationsAttribute(): int
    {
        return $this->organisations()->count();
    }

    /**
     * Obtenir le nombre de documents requis
     */
    public function getNbDocumentsRequisAttribute(): int
    {
        return $this->documentTypes()->count();
    }

    /**
     * Obtenir le nombre de documents obligatoires
     */
    public function getNbDocumentsObligatoiresAttribute(): int
    {
        return $this->documentTypesObligatoires()->count();
    }

    /**
     * Obtenir le badge HTML pour l'affichage
     */
    public function getBadgeHtmlAttribute(): string
    {
        $statut = $this->is_active ? 'Actif' : 'Inactif';
        $class = $this->is_active ? 'success' : 'secondary';
        
        return "<span class='badge bg-{$class}'>{$statut}</span>";
    }

    /**
     * ========================================
     * MÉTHODES MÉTIER
     * ========================================
     */

    /**
     * Vérifier si un nombre de fondateurs est valide
     */
    public function hasMinimumFondateurs(int $nbFondateurs): bool
    {
        return $nbFondateurs >= $this->nb_min_fondateurs_majeurs;
    }

    /**
     * Vérifier si un nombre d'adhérents est valide
     */
    public function hasMinimumAdherents(int $nbAdherents): bool
    {
        return $nbAdherents >= $this->nb_min_adherents_creation;
    }

    /**
     * Obtenir les règles de validation pour ce type
     */
    public function getValidationRules(): array
    {
        return [
            'nb_fondateurs' => [
                'required',
                'integer',
                "min:{$this->nb_min_fondateurs_majeurs}"
            ],
            'nb_adherents' => [
                'required',
                'integer',
                "min:{$this->nb_min_adherents_creation}"
            ],
        ];
    }

    /**
     * Attacher un type de document avec configuration
     */
    public function attachDocumentType(
        int $documentTypeId,
        bool $isObligatoire = true,
        int $ordre = 0,
        ?string $aideTexte = null
    ): void {
        $this->documentTypes()->attach($documentTypeId, [
            'is_obligatoire' => $isObligatoire,
            'ordre' => $ordre,
            'aide_texte' => $aideTexte,
        ]);
    }

    /**
     * Détacher un type de document
     */
    public function detachDocumentType(int $documentTypeId): void
    {
        $this->documentTypes()->detach($documentTypeId);
    }

    /**
     * Synchroniser les types de documents
     */
    public function syncDocumentTypes(array $documentTypeIds): void
    {
        $this->documentTypes()->sync($documentTypeIds);
    }

    /**
     * Activer/Désactiver le type
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Obtenir les statistiques du type
     */
    public function getStatistics(): array
    {
        return [
            'nb_organisations' => $this->organisations()->count(),
            'nb_organisations_actives' => $this->organisationsActives()->count(),
            'nb_documents_requis' => $this->documentTypes()->count(),
            'nb_documents_obligatoires' => $this->documentTypesObligatoires()->count(),
            'nb_documents_facultatifs' => $this->documentTypesFacultatifs()->count(),
            'nb_templates' => $this->documentTemplates()->count(),
        ];
    }

    /**
     * ========================================
     * MÉTHODES STATIQUES
     * ========================================
     */

    /**
     * Obtenir tous les codes disponibles
     */
    public static function getCodes(): array
    {
        return [
            self::CODE_ASSOCIATION,
            self::CODE_ONG,
            self::CODE_PARTI_POLITIQUE,
            self::CODE_CONFESSION_RELIGIEUSE,
        ];
    }

    /**
     * Obtenir les types actifs pour un select
     */
    public static function getActiveOptions(): array
    {
        return self::actif()
            ->ordered()
            ->pluck('nom', 'id')
            ->toArray();
    }

    /**
     * Obtenir un type par son code
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    /**
     * ========================================
     * BOOT - Événements du modèle
     * ========================================
     */
    protected static function boot()
    {
        parent::boot();

        // Avant création : définir l'ordre automatiquement
        static::creating(function ($organisationType) {
            if (empty($organisationType->ordre)) {
                $maxOrdre = self::max('ordre');
                $organisationType->ordre = ($maxOrdre ?? 0) + 1;
            }
        });

        // Avant suppression : vérifier s'il y a des organisations liées
        static::deleting(function ($organisationType) {
            if ($organisationType->organisations()->count() > 0) {
                throw new \Exception(
                    "Impossible de supprimer ce type : {$organisationType->organisations()->count()} organisation(s) y sont liées."
                );
            }
        });
    }
}