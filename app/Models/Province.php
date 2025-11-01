<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
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

    // Relations hiérarchiques descendantes
    public function departements()
    {
        return $this->hasMany(Departement::class)
                   ->orderBy('ordre_affichage')
                   ->orderBy('nom');
    }

    public function departementsActifs()
    {
        return $this->departements()->where('is_active', true);
    }

    // Relations vers les tables métier via références
    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'province_ref_id');
    }

    public function adherents()
    {
        return $this->hasMany(Adherent::class, 'province_ref_id');
    }

    public function etablissements()
    {
        return $this->hasMany(Etablissement::class, 'province_ref_id');
    }

    public function fondateurs()
    {
        return $this->hasMany(Fondateur::class, 'province_ref_id');
    }

    // ============================================================================
    // SCOPES - SECTION CORRIGÉE
    // ============================================================================
    
    /**
     * Scope pour les provinces actives
     * Alias standardisé : active()
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les provinces actives
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

    public function scopeParNom(Builder $query)
    {
        return $query->orderBy('nom');
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

    // Méthodes utilitaires
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
    public function isCodeUnique($code, $excludeId = null)
    {
        $query = static::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->doesntExist();
    }

    // Méthodes statistiques
    public function getStatistiques()
    {
        return [
            'departements' => $this->departements()->count(),
            'departements_actifs' => $this->departementsActifs()->count(),
            'organisations' => $this->getNombreOrganisations(),
            'adherents' => $this->getNombreAdherents(),
            'etablissements' => $this->getNombreEtablissements(),
            'superficie' => $this->superficie_km2,
            'population' => $this->population_estimee,
            'densite' => $this->densite,
        ];
    }

    // Override du boot pour gestion automatique
    protected static function boot()
    {
        parent::boot();

        // Auto-génération du code si non fourni
        static::creating(function ($province) {
            if (empty($province->code)) {
                $province->code = static::generateCode($province->nom);
            }
        });

        // Validation avant sauvegarde
        static::saving(function ($province) {
            // Validation du code unique
            if (!$province->isCodeUnique($province->code, $province->id)) {
                throw new \InvalidArgumentException("Le code '{$province->code}' existe déjà.");
            }
        });
    }

    // Génération automatique du code
    protected static function generateCode($nom)
    {
        $code = strtoupper(substr(trim($nom), 0, 3));
        $counter = 1;
        $baseCode = $code;
        
        while (!static::where('code', $code)->doesntExist()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    // Recherche
    public function scopeRecherche(Builder $query, $terme)
    {
        return $query->where(function ($q) use ($terme) {
            $q->where('nom', 'LIKE', "%{$terme}%")
              ->orWhere('code', 'LIKE', "%{$terme}%")
              ->orWhere('chef_lieu', 'LIKE', "%{$terme}%")
              ->orWhere('description', 'LIKE', "%{$terme}%");
        });
    }

    public function __toString()
    {
        return $this->nom;
    }
}