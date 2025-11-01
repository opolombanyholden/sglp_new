<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'nom',
        'code',
        'chef_lieu',
        'description',
        'superficie_km2',
        'population_estimee',
        'latitude',
        'longitude',
        'metadata',
        'is_active',
        'ordre_affichage',
    ];

    protected $casts = [
        'province_id' => 'integer',
        'superficie_km2' => 'decimal:2',
        'population_estimee' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'ordre_affichage' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'ordre_affichage' => 0,
    ];

    // Relations hiérarchiques
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    // Relations vers subdivisions urbaines
    public function communesVilles()
    {
        return $this->hasMany(CommuneVille::class)
                   ->orderBy('ordre_affichage')
                   ->orderBy('nom');
    }

    public function communesVillesActives()
    {
        return $this->communesVilles()->where('is_active', true);
    }

    // Relations vers subdivisions rurales
    public function cantons()
    {
        return $this->hasMany(Canton::class)
                   ->orderBy('ordre_affichage')
                   ->orderBy('nom');
    }

    public function cantonsActifs()
    {
        return $this->cantons()->where('is_active', true);
    }

    // Relations vers les tables métier via références
    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'departement_ref_id');
    }

    public function adherents()
    {
        return $this->hasMany(Adherent::class, 'departement_ref_id');
    }

    public function etablissements()
    {
        return $this->hasMany(Etablissement::class, 'departement_ref_id');
    }

    public function fondateurs()
    {
        return $this->hasMany(Fondateur::class, 'departement_ref_id');
    }

    // ============================================================================
    // SCOPES - SECTION CORRIGÉE
    // ============================================================================
    
    /**
     * Scope pour les départements actifs
     * Alias standardisé : active()
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les départements actifs
     * Alias original conservé pour compatibilité
     */
    public function scopeActif(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordonné pour affichage
     * Alias standardisé : ordered()
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('ordre_affichage')->orderBy('nom');
    }

    /**
     * Scope ordonné pour affichage
     * Alias original conservé pour compatibilité
     */
    public function scopeParOrdre(Builder $query)
    {
        return $query->orderBy('ordre_affichage')->orderBy('nom');
    }

    public function scopeParProvince(Builder $query, $provinceId)
    {
        return $query->where('province_id', $provinceId);
    }

    public function scopeAvecProvince(Builder $query)
    {
        return $query->with('province');
    }

    // Mutateurs
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = ucfirst(trim($value));
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function setChefLieuAttribute($value)
    {
        $this->attributes['chef_lieu'] = $value ? ucfirst(trim($value)) : null;
    }

    // Accesseurs
    public function getNomCompletAttribute()
    {
        return $this->nom . ' (' . $this->province->nom . ')';
    }

    public function getPopulationFormatteeAttribute()
    {
        return $this->population_estimee 
            ? number_format($this->population_estimee, 0, '.', ' ') . ' habitants'
            : 'Non renseigné';
    }

    public function getSuperficieFormatteeAttribute()
    {
        return $this->superficie_km2 
            ? number_format($this->superficie_km2, 0, '.', ' ') . ' km²'
            : 'Non renseigné';
    }

    public function getDensiteAttribute()
    {
        if ($this->superficie_km2 && $this->population_estimee) {
            return round($this->population_estimee / $this->superficie_km2, 2);
        }
        return null;
    }

    public function getDensiteFormatteeAttribute()
    {
        return $this->densite 
            ? number_format($this->densite, 2, ',', ' ') . ' hab/km²'
            : 'Non calculable';
    }

    // Méthodes utilitaires pour les subdivisions
    public function getNombreCommunesVilles()
    {
        return $this->communesVilles()->count();
    }

    public function getNombreCantons()
    {
        return $this->cantons()->count();
    }

    public function getNombreSubdivisions()
    {
        return $this->getNombreCommunesVilles() + $this->getNombreCantons();
    }

    public function hasSubdivisionsUrbaines()
    {
        return $this->getNombreCommunesVilles() > 0;
    }

    public function hasSubdivisionsRurales()
    {
        return $this->getNombreCantons() > 0;
    }

    public function getTypeSubdivisionAttribute()
    {
        $urbaines = $this->hasSubdivisionsUrbaines();
        $rurales = $this->hasSubdivisionsRurales();
        
        if ($urbaines && $rurales) {
            return 'mixte';
        } elseif ($urbaines) {
            return 'urbain';
        } elseif ($rurales) {
            return 'rural';
        }
        
        return 'non défini';
    }

    // Méthodes utilitaires métier
    public function getNombreOrganisations()
    {
        return $this->organisations()->count();
    }

    public function getNombreAdherents()
    {
        return $this->adherents()->count();
    }

    public function getNombreEtablissements()
    {
        return $this->etablissements()->count();
    }

    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function getCoordinatesAttribute()
    {
        if ($this->hasCoordinates()) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude
            ];
        }
        return null;
    }

    // Validation métier
    public function isCodeUniqueInProvince($code, $provinceId, $excludeId = null)
    {
        $query = static::where('code', $code)->where('province_id', $provinceId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->doesntExist();
    }

    // Méthodes statistiques
    public function getStatistiques()
    {
        return [
            'communes_villes' => $this->getNombreCommunesVilles(),
            'communes_villes_actives' => $this->communesVillesActives()->count(),
            'cantons' => $this->getNombreCantons(),
            'cantons_actifs' => $this->cantonsActifs()->count(),
            'total_subdivisions' => $this->getNombreSubdivisions(),
            'type_subdivision' => $this->type_subdivision,
            'organisations' => $this->getNombreOrganisations(),
            'adherents' => $this->getNombreAdherents(),
            'etablissements' => $this->getNombreEtablissements(),
            'superficie' => $this->superficie_km2,
            'population' => $this->population_estimee,
            'densite' => $this->densite,
        ];
    }

    // Méthodes de recherche et filtrage
    public function scopeRecherche(Builder $query, $terme)
    {
        return $query->where(function ($q) use ($terme) {
            $q->where('nom', 'LIKE', "%{$terme}%")
              ->orWhere('code', 'LIKE', "%{$terme}%")
              ->orWhere('chef_lieu', 'LIKE', "%{$terme}%")
              ->orWhere('description', 'LIKE', "%{$terme}%")
              ->orWhereHas('province', function ($subQ) use ($terme) {
                  $subQ->where('nom', 'LIKE', "%{$terme}%");
              });
        });
    }

    public function scopeParTypeSubdivision(Builder $query, $type)
    {
        switch ($type) {
            case 'urbain':
                return $query->whereHas('communesVilles');
            case 'rural':
                return $query->whereHas('cantons');
            case 'mixte':
                return $query->whereHas('communesVilles')
                            ->whereHas('cantons');
            default:
                return $query;
        }
    }

    // Override du boot pour gestion automatique
    protected static function boot()
    {
        parent::boot();

        // Auto-génération du code si non fourni
        static::creating(function ($departement) {
            if (empty($departement->code)) {
                $departement->code = static::generateCode($departement->nom, $departement->province_id);
            }
        });

        // Validation avant sauvegarde
        static::saving(function ($departement) {
            // Validation du code unique dans la province
            if (!$departement->isCodeUniqueInProvince($departement->code, $departement->province_id, $departement->id)) {
                throw new \InvalidArgumentException("Le code '{$departement->code}' existe déjà dans cette province.");
            }
        });
    }

    // Génération automatique du code
    protected static function generateCode($nom, $provinceId)
    {
        $province = Province::find($provinceId);
        $prefixe = $province ? substr($province->code, 0, 2) : 'XX';
        $code = $prefixe . strtoupper(substr(trim($nom), 0, 3));
        $counter = 1;
        $baseCode = $code;
        
        while (!static::where('code', $code)->where('province_id', $provinceId)->doesntExist()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    // Méthodes pour l'API
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'chef_lieu' => $this->chef_lieu,
            'province_id' => $this->province_id,
            'province_nom' => $this->province->nom,
            'nom_complet' => $this->nom_complet,
            'is_active' => $this->is_active,
            'type_subdivision' => $this->type_subdivision,
            'subdivisions_count' => $this->getNombreSubdivisions(),
        ];
    }

    public function __toString()
    {
        return $this->nom_complet;
    }
}