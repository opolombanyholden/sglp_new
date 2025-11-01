<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arrondissement extends Model
{
    use SoftDeletes;

    protected $table = 'arrondissements';

    protected $fillable = [
        'commune_ville_id',
        'nom',
        'code',
        'numero_arrondissement',
        'description',
        'superficie_km2',
        'population_estimee',
        'latitude',
        'longitude',
        'delegue',
        'telephone',
        'email',
        'limites_geographiques',
        'services_publics',
        'equipements',
        'metadata',
        'is_active',
        'ordre_affichage'
    ];

    protected $casts = [
        'superficie_km2' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population_estimee' => 'integer',
        'numero_arrondissement' => 'integer',
        'services_publics' => 'json',
        'equipements' => 'json',
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
     * Relation vers Commune/Ville (Many-to-One)
     */
    public function communeVille(): BelongsTo
    {
        return $this->belongsTo(CommuneVille::class, 'commune_ville_id');
    }

    /**
     * Relation vers Département via CommuneVille
     */
    public function departement()
    {
        return $this->communeVille->departement();
    }

    /**
     * Relation vers Province via CommuneVille → Département
     */
    public function province()
    {
        return $this->communeVille->departement->province();
    }

    /**
     * Relation vers Localités (Quartiers) (One-to-Many)
     */
    public function localites(): HasMany
    {
        return $this->hasMany(Localite::class, 'arrondissement_id')
                    ->where('type', 'quartier')
                    ->where('is_active', true)
                    ->orderBy('ordre_affichage');
    }

    /**
     * Alias pour les quartiers (localités urbaines)
     */
    public function quartiers(): HasMany
    {
        return $this->localites();
    }

    /**
     * Relation vers Organisations (One-to-Many)
     */
    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'arrondissement_ref_id');
    }

    /**
     * Relation vers Adherents (One-to-Many)
     */
    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class, 'arrondissement_ref_id');
    }

    /**
     * Relation vers Etablissements (One-to-Many)
     */
    public function etablissements(): HasMany
    {
        return $this->hasMany(Etablissement::class, 'arrondissement_ref_id');
    }

    /**
     * Relation vers Fondateurs (One-to-Many)
     */
    public function fondateurs(): HasMany
    {
        return $this->hasMany(Fondateur::class, 'arrondissement_ref_id');
    }

    // Scopes
    /**
     * Scope pour les arrondissements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par commune/ville
     */
    public function scopeParCommuneVille($query, $communeVilleId)
    {
        return $query->where('commune_ville_id', $communeVilleId);
    }

    /**
     * Scope par département
     */
    public function scopeParDepartement($query, $departementId)
    {
        return $query->whereHas('communeVille', function($q) use ($departementId) {
            $q->where('departement_id', $departementId);
        });
    }

    /**
     * Scope par numéro d'arrondissement
     */
    public function scopeParNumero($query, $numero)
    {
        return $query->where('numero_arrondissement', $numero);
    }

    /**
     * Scope ordonné pour affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('numero_arrondissement')->orderBy('ordre_affichage')->orderBy('nom');
    }

    // Accesseurs
    /**
     * Nom complet avec numéro
     */
    public function getNomCompletAttribute()
    {
        if ($this->numero_arrondissement) {
            $numero = $this->numero_arrondissement === 1 ? '1er' : $this->numero_arrondissement . 'ème';
            return "{$numero} Arrondissement {$this->nom}";
        }
        
        return "Arrondissement {$this->nom}";
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
            'numero' => $this->numero_arrondissement,
            'commune_ville' => $this->communeVille->nom ?? null,
            'type_commune_ville' => $this->communeVille->type ?? null,
            'departement' => $this->communeVille->departement->nom ?? null,
            'province' => $this->communeVille->departement->province->nom ?? null
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
     * Délégué avec informations de contact
     */
    public function getDelegueCompletAttribute()
    {
        $delegue = $this->delegue ?? 'Non assigné';
        
        if ($this->telephone) {
            $delegue .= " ({$this->telephone})";
        }
        
        return $delegue;
    }

    // Méthodes
    /**
     * Vérifier si l'arrondissement a des localités/quartiers
     */
    public function hasLocalites(): bool
    {
        return $this->localites()->count() > 0;
    }

    /**
     * Compter le nombre de quartiers
     */
    public function countQuartiers(): int
    {
        return $this->quartiers()->count();
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
            $communeCode = $this->communeVille->code ?? 'COM';
            $numero = str_pad($this->numero_arrondissement ?? 1, 2, '0', STR_PAD_LEFT);
            
            return $communeCode . 'A' . $numero;
        }
        
        return $this->code;
    }

    /**
     * Obtenir le numéro d'arrondissement suivant pour cette commune/ville
     */
    public static function getNextNumeroForCommuneVille($communeVilleId): int
    {
        $maxNumero = static::where('commune_ville_id', $communeVilleId)
                          ->max('numero_arrondissement');
        
        return ($maxNumero ?? 0) + 1;
    }

    /**
     * Services publics sous forme de liste
     */
    public function getServicesPublicsListAttribute()
    {
        if (is_array($this->services_publics)) {
            return implode(', ', $this->services_publics);
        }
        
        return $this->services_publics ?? '';
    }

    /**
     * Équipements sous forme de liste
     */
    public function getEquipementsListAttribute()
    {
        if (is_array($this->equipements)) {
            return implode(', ', $this->equipements);
        }
        
        return $this->equipements ?? '';
    }

    /**
     * Vérifier si l'arrondissement peut être supprimé
     */
    public function canBeDeleted(): bool
    {
        // Ne peut pas être supprimé s'il a des localités/quartiers
        if ($this->hasLocalites()) {
            return false;
        }

        // Ne peut pas être supprimé s'il a des organisations liées
        if ($this->countOrganisations() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir les raisons pour lesquelles l'arrondissement ne peut pas être supprimé
     */
    public function getDeletionBlockersAttribute(): array
    {
        $blockers = [];

        if ($this->hasLocalites()) {
            $count = $this->countQuartiers();
            $blockers[] = "Contient {$count} quartier(s)";
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

        static::creating(function ($arrondissement) {
            // Générer le code automatiquement si non fourni
            if (empty($arrondissement->code)) {
                $arrondissement->code = $arrondissement->generateCode();
            }
            
            // Assigner le numéro d'arrondissement automatiquement si non fourni
            if (is_null($arrondissement->numero_arrondissement)) {
                $arrondissement->numero_arrondissement = static::getNextNumeroForCommuneVille($arrondissement->commune_ville_id);
            }
            
            // Ordre d'affichage par défaut
            if (is_null($arrondissement->ordre_affichage)) {
                $maxOrdre = static::where('commune_ville_id', $arrondissement->commune_ville_id)
                                ->max('ordre_affichage');
                $arrondissement->ordre_affichage = ($maxOrdre ?? 0) + 1;
            }
        });

        static::updating(function ($arrondissement) {
            // Validation des coordonnées si modifiées
            if ($arrondissement->isDirty(['latitude', 'longitude'])) {
                if (!$arrondissement->hasValidCoordinates() && 
                    (!is_null($arrondissement->latitude) || !is_null($arrondissement->longitude))) {
                    throw new \InvalidArgumentException('Coordonnées GPS invalides');
                }
            }
        });

        static::deleting(function ($arrondissement) {
            // Empêcher la suppression si des localités sont liées
            if (!$arrondissement->canBeDeleted()) {
                $blockers = implode(', ', $arrondissement->deletion_blockers);
                throw new \Exception("Impossible de supprimer l'arrondissement : {$blockers}");
            }
        });
    }
}