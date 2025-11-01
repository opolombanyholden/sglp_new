<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Canton extends Model
{
    use SoftDeletes;

    protected $table = 'cantons';

    protected $fillable = [
        'departement_id',
        'nom',
        'code',
        'chef_lieu',
        'description',
        'superficie_km2',
        'population_estimee',
        'latitude',
        'longitude',
        'chef_canton',
        'telephone_chef',
        'telephone_administration',
        'limites_geographiques',
        'ethnies_principales',
        'langues_parlees',
        'activites_economiques',
        'ressources_naturelles',
        'infrastructures',
        'services_publics',
        'acces_electricite',
        'acces_eau_potable',
        'reseau_telephonique',
        'metadata',
        'is_active',
        'ordre_affichage'
    ];

    protected $casts = [
        'superficie_km2' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population_estimee' => 'integer',
        'ethnies_principales' => 'json',
        'langues_parlees' => 'json',
        'activites_economiques' => 'json',
        'ressources_naturelles' => 'json',
        'infrastructures' => 'json',
        'services_publics' => 'json',
        'acces_electricite' => 'boolean',
        'acces_eau_potable' => 'boolean',
        'reseau_telephonique' => 'boolean',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'ordre_affichage' => 'integer'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relations
    /**
     * Relation vers Département (Many-to-One)
     */
    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    /**
     * Relation vers Province via Département
     */
    public function province()
    {
        return $this->departement->province();
    }

    /**
     * Relation vers Regroupements (One-to-Many)
     */
    public function regroupements(): HasMany
    {
        return $this->hasMany(Regroupement::class, 'canton_id')
                    ->where('is_active', true)
                    ->orderBy('ordre_affichage');
    }

    /**
     * Relation vers Organisations (One-to-Many)
     */
    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'canton_ref_id');
    }

    /**
     * Relation vers Adherents (One-to-Many)
     */
    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class, 'canton_ref_id');
    }

    /**
     * Relation vers Etablissements (One-to-Many)
     */
    public function etablissements(): HasMany
    {
        return $this->hasMany(Etablissement::class, 'canton_ref_id');
    }

    /**
     * Relation vers Fondateurs (One-to-Many)
     */
    public function fondateurs(): HasMany
    {
        return $this->hasMany(Fondateur::class, 'canton_ref_id');
    }

    // Scopes
    /**
     * Scope pour les cantons actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par département
     */
    public function scopeParDepartement($query, $departementId)
    {
        return $query->where('departement_id', $departementId);
    }

    /**
     * Scope avec accès électricité
     */
    public function scopeAvecElectricite($query)
    {
        return $query->where('acces_electricite', true);
    }

    /**
     * Scope avec accès eau potable
     */
    public function scopeAvecEauPotable($query)
    {
        return $query->where('acces_eau_potable', true);
    }

    /**
     * Scope avec réseau téléphonique
     */
    public function scopeAvecReseauTelephonique($query)
    {
        return $query->where('reseau_telephonique', true);
    }

    /**
     * Scope ordonné pour affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre_affichage')->orderBy('nom');
    }

    // Accesseurs
    /**
     * Nom complet avec chef-lieu
     */
    public function getNomCompletAttribute()
    {
        if ($this->chef_lieu) {
            return "Canton {$this->nom} (Chef-lieu: {$this->chef_lieu})";
        }
        
        return "Canton {$this->nom}";
    }

    /**
     * Statut d'affichage
     */
    public function getStatutAffichageAttribute()
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    /**
     * Information géographique complète
     */
    public function getInfoGeographiqueAttribute()
    {
        $info = [
            'nom' => $this->nom,
            'chef_lieu' => $this->chef_lieu,
            'departement' => $this->departement->nom ?? null,
            'province' => $this->departement->province->nom ?? null
        ];
        
        if ($this->latitude && $this->longitude) {
            $info['coordinates'] = [
                'lat' => $this->latitude,
                'lng' => $this->longitude
            ];
        }

        return $info;
    }

    /**
     * Niveau d'infrastructure
     */
    public function getNiveauInfrastructureAttribute()
    {
        $score = 0;
        $total = 3;

        if ($this->acces_electricite) $score++;
        if ($this->acces_eau_potable) $score++;
        if ($this->reseau_telephonique) $score++;

        $pourcentage = ($score / $total) * 100;

        if ($pourcentage >= 80) return 'Excellent';
        if ($pourcentage >= 60) return 'Bon';
        if ($pourcentage >= 40) return 'Moyen';
        if ($pourcentage >= 20) return 'Faible';
        
        return 'Très faible';
    }

    /**
     * Chef canton avec contact
     */
    public function getChefCantonCompletAttribute()
    {
        $chef = $this->chef_canton ?? 'Non assigné';
        
        if ($this->telephone_chef) {
            $chef .= " ({$this->telephone_chef})";
        }
        
        return $chef;
    }

    // Méthodes
    /**
     * Vérifier si le canton a des regroupements
     */
    public function hasRegroupements(): bool
    {
        return $this->regroupements()->count() > 0;
    }

    /**
     * Compter le nombre de regroupements
     */
    public function countRegroupements(): int
    {
        return $this->regroupements()->count();
    }

    /**
     * Compter le nombre de villages (via regroupements)
     */
    public function countVillages(): int
    {
        return $this->regroupements()->withCount('localites')->get()->sum('localites_count');
    }

    /**
     * Compter le nombre d'organisations rattachées
     */
    public function countOrganisations(): int
    {
        return $this->organisations()->count();
    }

    /**
     * Compter le nombre d'adhérents rattachés
     */
    public function countAdherents(): int
    {
        return $this->adherents()->count();
    }

    /**
     * Vérifier la validité des coordonnées GPS
     */
    public function hasValidCoordinates(): bool
    {
        return !is_null($this->latitude) && 
               !is_null($this->longitude) &&
               $this->latitude >= -90 && $this->latitude <= 90 &&
               $this->longitude >= -180 && $this->longitude <= 180;
    }

    /**
     * Générer le code automatiquement si non fourni
     */
    public function generateCode(): string
    {
        if (empty($this->code)) {
            $deptCode = $this->departement->code ?? 'DEP';
            $nameCode = strtoupper(substr($this->nom, 0, 3));
            
            return $deptCode . 'C' . $nameCode;
        }
        
        return $this->code;
    }

    /**
     * Ethnies principales sous forme de liste
     */
    public function getEthniesListAttribute()
    {
        if (is_array($this->ethnies_principales)) {
            return implode(', ', $this->ethnies_principales);
        }
        
        return $this->ethnies_principales ?? '';
    }

    /**
     * Langues parlées sous forme de liste
     */
    public function getLanguesListAttribute()
    {
        if (is_array($this->langues_parlees)) {
            return implode(', ', $this->langues_parlees);
        }
        
        return $this->langues_parlees ?? '';
    }

    /**
     * Activités économiques sous forme de liste
     */
    public function getActivitesEconomiquesListAttribute()
    {
        if (is_array($this->activites_economiques)) {
            return implode(', ', $this->activites_economiques);
        }
        
        return $this->activites_economiques ?? '';
    }

    /**
     * Vérifier si le canton peut être supprimé
     */
    public function canBeDeleted(): bool
    {
        // Ne peut pas être supprimé s'il a des regroupements
        if ($this->hasRegroupements()) {
            return false;
        }

        // Ne peut pas être supprimé s'il a des organisations liées
        if ($this->countOrganisations() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir les raisons pour lesquelles le canton ne peut pas être supprimé
     */
    public function getDeletionBlockersAttribute(): array
    {
        $blockers = [];

        if ($this->hasRegroupements()) {
            $count = $this->countRegroupements();
            $blockers[] = "Contient {$count} regroupement(s)";
        }

        if ($this->countOrganisations() > 0) {
            $count = $this->countOrganisations();
            $blockers[] = "Lié à {$count} organisation(s)";
        }

        return $blockers;
    }

    /**
     * Validation des données avant sauvegarde
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($canton) {
            // Générer le code automatiquement si non fourni
            if (empty($canton->code)) {
                $canton->code = $canton->generateCode();
            }
            
            // Ordre d'affichage par défaut
            if (is_null($canton->ordre_affichage)) {
                $maxOrdre = static::where('departement_id', $canton->departement_id)
                                ->max('ordre_affichage');
                $canton->ordre_affichage = ($maxOrdre ?? 0) + 1;
            }
        });

        static::updating(function ($canton) {
            // Validation des coordonnées si modifiées
            if ($canton->isDirty(['latitude', 'longitude'])) {
                if (!$canton->hasValidCoordinates() && 
                    (!is_null($canton->latitude) || !is_null($canton->longitude))) {
                    throw new \InvalidArgumentException('Coordonnées GPS invalides');
                }
            }
        });

        static::deleting(function ($canton) {
            // Empêcher la suppression si des regroupements sont liés
            if (!$canton->canBeDeleted()) {
                $blockers = implode(', ', $canton->deletion_blockers);
                throw new \Exception("Impossible de supprimer le canton : {$blockers}");
            }
        });
    }
}