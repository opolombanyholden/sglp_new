<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommuneVille extends Model
{
    use SoftDeletes;

    protected $table = 'communes_villes';

    protected $fillable = [
        'departement_id',
        'nom',
        'code',
        'type',
        'statut',
        'description',
        'superficie_km2',
        'population_estimee',
        'latitude',
        'longitude',
        'maire',
        'date_creation',
        'telephone',
        'email',
        'site_web',
        'metadata',
        'is_active',
        'ordre_affichage'
    ];

    protected $casts = [
        'superficie_km2' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population_estimee' => 'integer',
        'date_creation' => 'date',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'ordre_affichage' => 'integer'
    ];

    protected $dates = [
        'date_creation',
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
     * Relation vers Arrondissements (One-to-Many)
     */
    public function arrondissements(): HasMany
    {
        return $this->hasMany(Arrondissement::class, 'commune_ville_id')
                    ->where('is_active', true)
                    ->orderBy('ordre_affichage');
    }

    /**
     * Relation vers Organisations (One-to-Many)
     */
    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'commune_ville_ref_id');
    }

    /**
     * Relation vers Adherents (One-to-Many)
     */
    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class, 'commune_ville_ref_id');
    }

    /**
     * Relation vers Etablissements (One-to-Many)
     */
    public function etablissements(): HasMany
    {
        return $this->hasMany(Etablissement::class, 'commune_ville_ref_id');
    }

    /**
     * Relation vers Fondateurs (One-to-Many)
     */
    public function fondateurs(): HasMany
    {
        return $this->hasMany(Fondateur::class, 'commune_ville_ref_id');
    }

    // Scopes
    /**
     * Scope pour les communes/villes actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les communes uniquement
     */
    public function scopeCommunes($query)
    {
        return $query->where('type', 'commune');
    }

    /**
     * Scope pour les villes uniquement
     */
    public function scopeVilles($query)
    {
        return $query->where('type', 'ville');
    }

    /**
     * Scope par département
     */
    public function scopeParDepartement($query, $departementId)
    {
        return $query->where('departement_id', $departementId);
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
     * Nom complet avec type
     */
    public function getNomCompletAttribute()
    {
        $typeLabel = $this->type === 'ville' ? 'Ville' : 'Commune';
        return "{$typeLabel} de {$this->nom}";
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
            'type' => $this->type,
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

    // Méthodes
    /**
     * Vérifier si la commune/ville a des arrondissements
     */
    public function hasArrondissements(): bool
    {
        return $this->arrondissements()->count() > 0;
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
            $typePrefix = $this->type === 'ville' ? 'V' : 'C';
            $nameCode = strtoupper(substr($this->nom, 0, 3));
            
            return $deptCode . $typePrefix . $nameCode;
        }
        
        return $this->code;
    }

    /**
     * Validation des données avant sauvegarde
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($communeVille) {
            if (empty($communeVille->code)) {
                $communeVille->code = $communeVille->generateCode();
            }
            
            if (is_null($communeVille->ordre_affichage)) {
                $maxOrdre = static::where('departement_id', $communeVille->departement_id)
                                ->max('ordre_affichage');
                $communeVille->ordre_affichage = ($maxOrdre ?? 0) + 1;
            }
        });

        static::updating(function ($communeVille) {
            // Validation des coordonnées si modifiées
            if ($communeVille->isDirty(['latitude', 'longitude'])) {
                if (!$communeVille->hasValidCoordinates() && 
                    (!is_null($communeVille->latitude) || !is_null($communeVille->longitude))) {
                    throw new \InvalidArgumentException('Coordonnées GPS invalides');
                }
            }
        });
    }
}