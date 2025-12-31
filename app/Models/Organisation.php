<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * MODÈLE ORGANISATION
 * 
 * Gère les organisations (associations, ONG, partis politiques, confessions religieuses)
 * 
 * @property int $id
 * @property int $user_id
 * @property int $organisation_type_id (NOUVEAU)
 * @property string $type (ANCIEN - à supprimer plus tard)
 * @property string $nom
 * @property string|null $sigle
 * @property string $objet
 * @property string $siege_social
 * @property string $province
 * @property string|null $departement
 * @property string|null $canton
 * @property string $prefecture
 * @property string|null $sous_prefecture
 * @property string|null $regroupement
 * @property string $zone_type
 * @property string|null $ville_commune
 * @property string|null $arrondissement
 * @property string|null $quartier
 * @property string|null $village
 * @property string|null $lieu_dit
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $email
 * @property string $telephone
 * @property string|null $telephone_secondaire
 * @property string|null $site_web
 * @property string|null $numero_recepisse
 * @property \Carbon\Carbon $date_creation
 * @property string $statut
 * @property bool $is_active
 * @property int $nombre_adherents_min
 * @property array|null $organes_gestion
 * 
 * Projet : SGLP
 * Compatible : PHP 8.3, Laravel 10+
 */
class Organisation extends Model
{
    use HasFactory;

    /**
     * ========================================
     * FILLABLE - Colonnes assignables en masse
     * ========================================
     */
    protected $fillable = [
        'user_id',
        'organisation_type_id',  // ← NOUVEAU système
        'domaine_activite_id',   // ← Domaine d'activité (référentiel)
        'type',                   // ← ANCIEN système (à retirer plus tard)
        'nom',
        'sigle',
        'objet',
        'siege_social',
        'province',
        'departement',
        'canton',
        'prefecture',
        'sous_prefecture',
        'regroupement',
        'zone_type',
        'ville_commune',
        'arrondissement',
        'quartier',
        'village',
        'lieu_dit',
        'latitude',
        'longitude',
        'province_ref_id',
        'departement_ref_id',
        'commune_ville_ref_id',
        'arrondissement_ref_id',
        'canton_ref_id',
        'regroupement_ref_id',
        'localite_ref_id',
        'email',
        'telephone',
        'telephone_secondaire',
        'site_web',
        'numero_recepisse',
        'date_creation',
        'statut',
        'is_active',
        'nombre_adherents_min',
        'has_anomalies_majeures',
        'organes_gestion'
    ];

    /**
     * ========================================
     * CASTS - Conversion automatique des types
     * ========================================
     */
    protected $casts = [
        'date_creation' => 'date',
        'is_active' => 'boolean',
        'has_anomalies_majeures' => 'boolean',
        'organes_gestion' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ========================================
     * CONSTANTES - TYPES D'ORGANISATION (ANCIEN SYSTÈME)
     * ========================================
     */
    const TYPE_ASSOCIATION = 'association';
    const TYPE_ONG = 'ong';
    const TYPE_PARTI = 'parti_politique';
    const TYPE_CONFESSION = 'confession_religieuse';

    /**
     * ========================================
     * CONSTANTES - STATUTS
     * ========================================
     */
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_SOUMIS = 'soumis';
    const STATUT_EN_VALIDATION = 'en_validation';
    const STATUT_APPROUVE = 'approuve';
    const STATUT_REJETE = 'rejete';
    const STATUT_SUSPENDU = 'suspendu';
    const STATUT_RADIE = 'radie';

    /**
     * ========================================
     * CONSTANTES - ZONES
     * ========================================
     */
    const ZONE_URBAINE = 'urbaine';
    const ZONE_RURALE = 'rurale';

    /**
     * ========================================
     * RELATIONS
     * ========================================
     */

    /**
     * Relation : Utilisateur créateur (opérateur)
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : Type d'organisation (NOUVEAU SYSTÈME)
     * 
     * @return BelongsTo
     */
    public function organisationType(): BelongsTo
    {
        return $this->belongsTo(OrganisationType::class, 'organisation_type_id');
    }

    /**
     * Relation : Domaine d'activité
     * 
     * @return BelongsTo
     */
    public function domaineActivite(): BelongsTo
    {
        return $this->belongsTo(DomaineActivite::class, 'domaine_activite_id');
    }

    /**
     * Relation : Dossiers de l'organisation
     * 
     * @return HasMany
     */
    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Relation : Dossier actif de l'organisation
     * 
     * @return HasOne
     */
    public function dossierActif(): HasOne
    {
        return $this->hasOne(Dossier::class)
            ->where('is_active', true)
            ->latest();
    }

    /**
     * Relation : Fondateurs de l'organisation
     * 
     * @return HasMany
     */
    public function fondateurs(): HasMany
    {
        return $this->hasMany(Fondateur::class);
    }

    /**
     * Relation : Membres du bureau de l'organisation
     * 
     * @return HasMany
     */
    public function membresBureau(): HasMany
    {
        return $this->hasMany(MembreBureau::class);
    }

    /**
     * Relation : Membres du bureau à afficher sur le récépissé (max 3)
     * 
     * @return HasMany
     */
    public function membresBureauPourRecepisse(): HasMany
    {
        return $this->hasMany(MembreBureau::class)
            ->where('afficher_recepisse', true)
            ->orderBy('ordre')
            ->limit(3);
    }

    /**
     * Relation : Adhérents de l'organisation
     * 
     * @return HasMany
     */
    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class);
    }

    /**
     * Relation : Adhérents actifs de l'organisation
     * 
     * @return HasMany
     */
    public function adherentsActifs(): HasMany
    {
        return $this->hasMany(Adherent::class)
            ->where('is_active', true);
    }

    public function personnes()
    {
        // Retourner une collection fusionnant fondateurs et adhérents
        $fondateurs = $this->fondateurs()->get()->map(function ($f) {
            $f->setAttribute('type_personne', 'fondateur');
            return $f;
        });

        $adherents = $this->adherents()->get()->map(function ($a) {
            $a->setAttribute('type_personne', 'adherent');
            return $a;
        });

        return $fondateurs->merge($adherents);
    }

    /**
     * Relation : Établissements de l'organisation
     * 
     * @return HasMany
     */
    public function etablissements(): HasMany
    {
        return $this->hasMany(Etablissement::class);
    }

    /**
     * Relation : Membres des organes de gestion
     * 
     * @return HasMany
     */
    public function organeMembres(): HasMany
    {
        return $this->hasMany(OrganeMember::class);
    }

    /**
     * Relation : Déclarations de l'organisation
     * 
     * @return HasMany
     */
    public function declarations(): HasMany
    {
        return $this->hasMany(Declaration::class);
    }

    /**
     * ========================================
     * SCOPES - Requêtes réutilisables
     * ========================================
     */

    /**
     * Scope : Organisations actives uniquement
     */
    public function scopeActives($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Par type (ancien système ENUM)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope : Par type d'organisation (nouveau système FK)
     */
    public function scopeByOrganisationType($query, int $organisationTypeId)
    {
        return $query->where('organisation_type_id', $organisationTypeId);
    }

    /**
     * Scope : Par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope : Organisations approuvées uniquement
     */
    public function scopeApprouvees($query)
    {
        return $query->where('statut', self::STATUT_APPROUVE);
    }

    /**
     * Scope : Par province
     */
    public function scopeByProvince($query, string $province)
    {
        return $query->where('province', $province);
    }

    /**
     * Scope : Par zone (urbaine/rurale)
     */
    public function scopeByZoneType($query, string $zoneType)
    {
        return $query->where('zone_type', $zoneType);
    }

    /**
     * ========================================
     * MÉTHODES UTILITAIRES - TYPE
     * ========================================
     */

    /**
     * Vérifier si l'organisation est un parti politique
     */
    public function isPartiPolitique(): bool
    {
        // Utilise le nouveau système en priorité
        if ($this->organisationType) {
            return $this->organisationType->code === self::TYPE_PARTI;
        }
        // Fallback sur l'ancien système
        return $this->type === self::TYPE_PARTI;
    }

    /**
     * Vérifier si l'organisation est une confession religieuse
     */
    public function isConfessionReligieuse(): bool
    {
        if ($this->organisationType) {
            return $this->organisationType->code === self::TYPE_CONFESSION;
        }
        return $this->type === self::TYPE_CONFESSION;
    }

    /**
     * Vérifier si l'organisation est une association
     */
    public function isAssociation(): bool
    {
        if ($this->organisationType) {
            return $this->organisationType->code === self::TYPE_ASSOCIATION;
        }
        return $this->type === self::TYPE_ASSOCIATION;
    }

    /**
     * Vérifier si l'organisation est une ONG
     */
    public function isOng(): bool
    {
        if ($this->organisationType) {
            return $this->organisationType->code === self::TYPE_ONG;
        }
        return $this->type === self::TYPE_ONG;
    }

    /**
     * ========================================
     * MÉTHODES UTILITAIRES - STATUT
     * ========================================
     */

    /**
     * Vérifier si l'organisation est approuvée
     */
    public function isApprouvee(): bool
    {
        return $this->statut === self::STATUT_APPROUVE;
    }

    /**
     * Vérifier si l'organisation peut ajouter des adhérents
     */
    public function canAddAdherent(): bool
    {
        // Pour les partis politiques et confessions religieuses, 
        // vérifier s'il n'y a pas déjà une organisation active
        if (in_array($this->type, [self::TYPE_PARTI, self::TYPE_CONFESSION])) {
            return $this->is_active && $this->isApprouvee();
        }

        return $this->isApprouvee();
    }

    /**
     * Vérifier si l'organisation a le nombre minimum d'adhérents
     */
    public function hasMinimumAdherents(): bool
    {
        return $this->adherentsActifs()->count() >= $this->nombre_adherents_min;
    }

    /**
     * Vérifier si l'organisation est en brouillon
     */
    public function isBrouillon(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si l'organisation est soumise
     */
    public function isSoumise(): bool
    {
        return $this->statut === self::STATUT_SOUMIS;
    }

    /**
     * Vérifier si l'organisation est en validation
     */
    public function isEnValidation(): bool
    {
        return $this->statut === self::STATUT_EN_VALIDATION;
    }

    /**
     * Vérifier si l'organisation est rejetée
     */
    public function isRejetee(): bool
    {
        return $this->statut === self::STATUT_REJETE;
    }

    /**
     * Vérifier si l'organisation est suspendue
     */
    public function isSuspendue(): bool
    {
        return $this->statut === self::STATUT_SUSPENDU;
    }

    /**
     * Vérifier si l'organisation est radiée
     */
    public function isRadiee(): bool
    {
        return $this->statut === self::STATUT_RADIE;
    }

    /**
     * ========================================
     * ACCESSEURS (GETTERS)
     * ========================================
     */

    /**
     * Obtenir l'adresse complète formatée
     */
    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([
            $this->siege_social,
            $this->quartier,
            $this->arrondissement,
            $this->ville_commune,
            $this->village,
            $this->sous_prefecture,
            $this->prefecture,
            $this->departement,
            $this->province
        ]);

        return implode(', ', $parts);
    }

    /**
     * Obtenir le label du type (utilise le nouveau système en priorité)
     */
    public function getTypeLabelAttribute(): string
    {
        // Utiliser le nouveau système en priorité
        if ($this->organisationType) {
            return $this->organisationType->nom;
        }

        // Fallback sur l'ancien système
        $labels = [
            self::TYPE_ASSOCIATION => 'Association',
            self::TYPE_ONG => 'ONG',
            self::TYPE_PARTI => 'Parti politique',
            self::TYPE_CONFESSION => 'Confession religieuse'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Obtenir la couleur du type (nouveau système)
     */
    public function getTypeCouleurAttribute(): ?string
    {
        return $this->organisationType?->couleur;
    }

    /**
     * Obtenir l'icône du type (nouveau système)
     */
    public function getTypeIconeAttribute(): ?string
    {
        return $this->organisationType?->icone;
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatutLabelAttribute(): string
    {
        $labels = [
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_SOUMIS => 'Soumis',
            self::STATUT_EN_VALIDATION => 'En validation',
            self::STATUT_APPROUVE => 'Approuvé',
            self::STATUT_REJETE => 'Rejeté',
            self::STATUT_SUSPENDU => 'Suspendu',
            self::STATUT_RADIE => 'Radié'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur du statut pour l'affichage (Bootstrap classes)
     */
    public function getStatutColorAttribute(): string
    {
        $colors = [
            self::STATUT_BROUILLON => 'secondary',
            self::STATUT_SOUMIS => 'info',
            self::STATUT_EN_VALIDATION => 'warning',
            self::STATUT_APPROUVE => 'success',
            self::STATUT_REJETE => 'danger',
            self::STATUT_SUSPENDU => 'dark',
            self::STATUT_RADIE => 'danger'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }

    /**
     * Obtenir le badge HTML du statut
     */
    public function getStatutBadgeAttribute(): string
    {
        $color = $this->statut_color;
        $label = $this->statut_label;

        return "<span class='badge bg-{$color}'>{$label}</span>";
    }

    /**
     * Obtenir le badge HTML du type
     */
    public function getTypeBadgeAttribute(): string
    {
        if ($this->organisationType) {
            $couleur = $this->organisationType->couleur;
            $nom = $this->organisationType->nom;
            $icone = $this->organisationType->icone;

            $iconeHtml = $icone ? "<i class='fas {$icone}'></i> " : '';

            return "<span class='badge' style='background-color: {$couleur}; color: white;'>{$iconeHtml}{$nom}</span>";
        }

        return "<span class='badge bg-secondary'>{$this->type_label}</span>";
    }

    /**
     * ========================================
     * MÉTHODES MÉTIER
     * ========================================
     */

    /**
     * Obtenir le nombre de fondateurs majeurs
     */
    /**
     * Obtenir le nombre de fondateurs majeurs
     */
    /**
     * Obtenir le nombre de fondateurs majeurs (>= 18 ans)
     * Calcul basé sur la date de naissance
     */
    public function getNombreFondateursMajeursAttribute(): int
    {
        return $this->fondateurs()
            ->whereNotNull('date_naissance')
            ->where(DB::raw('TIMESTAMPDIFF(YEAR, date_naissance, CURDATE())'), '>=', 18)
            ->count();
    }

    /**
     * Vérifier si l'organisation a le nombre minimum de fondateurs requis
     */
    public function hasMinimumFondateurs(): bool
    {
        if (!$this->organisationType) {
            return true; // Pas de validation si pas de type défini
        }

        return $this->nombre_fondateurs_majeurs >= $this->organisationType->nb_min_fondateurs_majeurs;
    }

    /**
     * Vérifier si l'organisation a le nombre minimum d'adhérents requis
     */
    public function hasMinimumAdherentsCreation(): bool
    {
        if (!$this->organisationType) {
            return true;
        }

        return $this->adherentsActifs()->count() >= $this->organisationType->nb_min_adherents_creation;
    }

    /**
     * Vérifier si l'organisation respecte toutes les règles métier
     */
    public function respecteReglesMetier(): bool
    {
        return $this->hasMinimumFondateurs() && $this->hasMinimumAdherentsCreation();
    }

    /**
     * ========================================
     * BOOT - Événements du modèle
     * ========================================
     */
    protected static function boot()
    {
        parent::boot();

        // Avant création : synchroniser type et organisation_type_id
        static::creating(function ($organisation) {
            // Si organisation_type_id est défini, mettre à jour le champ type
            if ($organisation->organisation_type_id) {
                $orgType = OrganisationType::find($organisation->organisation_type_id);
                if ($orgType) {
                    $organisation->type = $orgType->code;
                }
            }
            // Si type est défini mais pas organisation_type_id, trouver le type correspondant
            elseif ($organisation->type) {
                $orgType = OrganisationType::where('code', $organisation->type)->first();
                if ($orgType) {
                    $organisation->organisation_type_id = $orgType->id;
                }
            }
        });

        // Avant mise à jour : synchroniser type et organisation_type_id
        static::updating(function ($organisation) {
            if ($organisation->isDirty('organisation_type_id')) {
                $orgType = OrganisationType::find($organisation->organisation_type_id);
                if ($orgType) {
                    $organisation->type = $orgType->code;
                }
            } elseif ($organisation->isDirty('type')) {
                $orgType = OrganisationType::where('code', $organisation->type)->first();
                if ($orgType) {
                    $organisation->organisation_type_id = $orgType->id;
                }
            }
        });
    }
}