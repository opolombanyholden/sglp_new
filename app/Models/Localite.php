<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Localite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'localites';

    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        'arrondissement_id',
        'regroupement_id',
        'type',
        'nom',
        'code',
        'description',
        'population_estimee',
        'latitude',
        'longitude',
        'is_active',
        'ordre_affichage',
        'metadata'
    ];

    /**
     * Conversion des types d'attributs
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population_estimee' => 'integer',
        'is_active' => 'boolean',
        'ordre_affichage' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Types de localités
     */
    const TYPE_QUARTIER = 'quartier';
    const TYPE_VILLAGE = 'village';

    /**
     * Relations
     */
    
    /**
     * Relation : Une localité (quartier) appartient à un arrondissement
     */
    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissement_id');
    }

    /**
     * Relation : Une localité (village) appartient à un regroupement
     */
    public function regroupement()
    {
        return $this->belongsTo(Regroupement::class, 'regroupement_id');
    }

    /**
     * Relation : Les organisations liées à cette localité
     */
    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'localite_ref_id');
    }

    /**
     * Relation : Les adhérents liés à cette localité
     */
    public function adherents()
    {
        return $this->hasMany(Adherent::class, 'localite_ref_id');
    }

    /**
     * Scopes de requête
     */
    
    /**
     * Scope : Localités actives uniquement
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Localités inactives uniquement
     */
    public function scopeInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope : Filtrer les quartiers urbains
     */
    public function scopeQuartiers(Builder $query)
    {
        return $query->where('type', self::TYPE_QUARTIER);
    }

    /**
     * Scope : Filtrer les villages ruraux
     */
    public function scopeVillages(Builder $query)
    {
        return $query->where('type', self::TYPE_VILLAGE);
    }

    /**
     * Scope : Filtrer par arrondissement
     */
    public function scopeByArrondissement(Builder $query, $arrondissementId)
    {
        return $query->where('arrondissement_id', $arrondissementId);
    }

    /**
     * Scope : Filtrer par regroupement
     */
    public function scopeByRegroupement(Builder $query, $regroupementId)
    {
        return $query->where('regroupement_id', $regroupementId);
    }

    /**
     * Mutators & Accessors
     */
    
    /**
     * Formater le nom (première lettre en majuscule)
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = ucwords(strtolower(trim($value)));
    }
    
    /**
     * Formater le code en majuscules
     */
    public function setCodeAttribute($value)
    {
        if ($value) {
            $this->attributes['code'] = strtoupper(trim($value));
        }
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLibelleAttribute()
    {
        return $this->type === self::TYPE_QUARTIER ? 'Quartier' : 'Village';
    }

    /**
     * Obtenir le nom complet avec hiérarchie
     */
    public function getNomCompletAttribute()
    {
        if ($this->type === self::TYPE_QUARTIER && $this->arrondissement) {
            return "Quartier {$this->nom} - {$this->arrondissement->nom}";
        }
        
        if ($this->type === self::TYPE_VILLAGE && $this->regroupement) {
            return "Village {$this->nom} - {$this->regroupement->nom}";
        }
        
        return $this->nom;
    }

    /**
     * Obtenir le parcours géographique complet
     */
    public function getParcoursCompletAttribute()
    {
        if ($this->type === self::TYPE_QUARTIER && $this->arrondissement) {
            $arr = $this->arrondissement;
            $cv = $arr->communeVille;
            $dept = $cv ? $cv->departement : null;
            $prov = $dept ? $dept->province : null;
            
            return implode(' > ', array_filter([
                $prov ? $prov->nom : null,
                $dept ? $dept->nom : null,
                $cv ? $cv->nom : null,
                $arr->nom,
                "Quartier {$this->nom}"
            ]));
        }
        
        if ($this->type === self::TYPE_VILLAGE && $this->regroupement) {
            $reg = $this->regroupement;
            $canton = $reg->canton;
            $dept = $canton ? $canton->departement : null;
            $prov = $dept ? $dept->province : null;
            
            return implode(' > ', array_filter([
                $prov ? $prov->nom : null,
                $dept ? $dept->nom : null,
                $canton ? $canton->nom : null,
                $reg->nom,
                "Village {$this->nom}"
            ]));
        }
        
        return $this->nom;
    }

    /**
     * Méthodes utilitaires
     */
    
    /**
     * Vérifier si c'est un quartier urbain
     */
    public function isQuartier()
    {
        return $this->type === self::TYPE_QUARTIER;
    }

    /**
     * Vérifier si c'est un village rural
     */
    public function isVillage()
    {
        return $this->type === self::TYPE_VILLAGE;
    }

    /**
     * Générer un code unique automatiquement
     */
    public function genererCode()
    {
        if ($this->code) {
            return $this->code;
        }
        
        $prefix = $this->type === self::TYPE_QUARTIER ? 'QT' : 'VL';
        
        // Préfixe basé sur les 3 premières lettres du nom
        $nomPrefix = strtoupper(substr($this->nom, 0, 3));
        $nomPrefix = preg_replace('/[^A-Z]/', '', $nomPrefix);
        $nomPrefix = str_pad($nomPrefix, 3, 'X');
        
        // Numéro séquentiel
        if ($this->type === self::TYPE_QUARTIER && $this->arrondissement_id) {
            $numero = self::where('arrondissement_id', $this->arrondissement_id)
                ->where('type', self::TYPE_QUARTIER)
                ->count() + 1;
        } elseif ($this->type === self::TYPE_VILLAGE && $this->regroupement_id) {
            $numero = self::where('regroupement_id', $this->regroupement_id)
                ->where('type', self::TYPE_VILLAGE)
                ->count() + 1;
        } else {
            $numero = self::where('type', $this->type)->count() + 1;
        }
        
        return $prefix . '-' . $nomPrefix . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifier si la localité peut être supprimée
     */
    public function peutEtreSupprime()
    {
        // Vérifier s'il y a des organisations liées
        try {
            $organisations_count = \DB::table('organisations')
                ->where('localite_ref_id', $this->id)
                ->count();
            
            if ($organisations_count > 0) {
                return [
                    'peut_supprimer' => false,
                    'raison' => "Cette localité est référencée par {$organisations_count} organisation(s). Veuillez d'abord modifier ces références."
                ];
            }
        } catch (\Exception $e) {
            // Table organisations n'existe pas encore
        }
        
        // Vérifier s'il y a des adhérents liés
        try {
            $adherents_count = \DB::table('adherents')
                ->where('localite_ref_id', $this->id)
                ->count();
            
            if ($adherents_count > 0) {
                return [
                    'peut_supprimer' => false,
                    'raison' => "Cette localité est référencée par {$adherents_count} adhérent(s). Veuillez d'abord modifier ces références."
                ];
            }
        } catch (\Exception $e) {
            // Table adherents n'existe pas encore
        }
        
        return [
            'peut_supprimer' => true,
            'raison' => null
        ];
    }

    /**
     * Événements du modèle
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-génération du code si vide lors de la création
        static::creating(function ($localite) {
            if (!$localite->code) {
                $localite->code = $localite->genererCode();
            }
        });
    }

    /**
     * Règles de validation
     */
    public static function rules($id = null)
    {
        return [
            'type' => 'required|in:' . self::TYPE_QUARTIER . ',' . self::TYPE_VILLAGE,
            'arrondissement_id' => 'required_if:type,' . self::TYPE_QUARTIER . '|nullable|exists:arrondissements,id',
            'regroupement_id' => 'required_if:type,' . self::TYPE_VILLAGE . '|nullable|exists:regroupements,id',
            'nom' => 'required|string|min:2|max:255',
            'code' => 'nullable|string|max:35|unique:localites,code,' . $id,
            'description' => 'nullable|string|max:1000',
            'population_estimee' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'ordre_affichage' => 'nullable|integer|min:0'
        ];
    }

    /**
     * Options pour les types de localités
     */
    public static function getTypesOptions()
    {
        return [
            self::TYPE_QUARTIER => 'Quartier urbain',
            self::TYPE_VILLAGE => 'Village rural'
        ];
    }
}