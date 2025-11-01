<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Regroupement extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        'canton_id',
        'nom',
        'code',
        'description',
        'is_active',
        'ordre_affichage',
        'metadata'
    ];

    /**
     * Conversion des types d'attributs
     */
    protected $casts = [
        'is_active' => 'boolean',
        'ordre_affichage' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relations
     */
    
    /**
     * Relation : Un regroupement appartient à un canton
     */
    public function canton()
    {
        return $this->belongsTo(Canton::class);
    }
    
    /**
     * Relation : Accès au département via le canton
     */
    public function departement()
    {
        return $this->hasOneThrough(
            Departement::class, 
            Canton::class, 
            'id', 
            'id', 
            'canton_id', 
            'departement_id'
        );
    }

    /**
     * Scopes de requête
     */
    
    /**
     * Scope : Regroupements actifs uniquement
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope : Regroupements inactifs uniquement
     */
    public function scopeInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }
    
    /**
     * Scope : Filtrer par canton
     */
    public function scopeByCanton(Builder $query, $cantonId)
    {
        return $query->where('canton_id', $cantonId);
    }
    
    /**
     * Scope : Filtrer par département
     */
    public function scopeByDepartement(Builder $query, $departementId)
    {
        return $query->whereHas('canton', function ($q) use ($departementId) {
            $q->where('departement_id', $departementId);
        });
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
     * Obtenir la hiérarchie complète (Province > Département > Canton > Regroupement)
     */
    public function getHierarchieCompleteAttribute()
    {
        $canton = $this->canton;
        if (!$canton) return null;
        
        $departement = $canton->departement;
        $province = $departement ? $departement->province : null;
        
        return [
            'province' => $province ? $province->nom : null,
            'departement' => $departement ? $departement->nom : null,
            'canton' => $canton->nom,
            'regroupement' => $this->nom
        ];
    }

    /**
     * Méthodes utilitaires
     */
    
    /**
     * Générer un code unique automatiquement
     */
    public function genererCode()
    {
        if ($this->code) {
            return $this->code;
        }
        
        $canton = $this->canton;
        if (!$canton) {
            return 'REG-' . str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        }
        
        // Préfixe basé sur les 3 premières lettres du nom
        $prefix = strtoupper(substr($this->nom, 0, 3));
        $prefix = preg_replace('/[^A-Z]/', '', $prefix);
        $prefix = str_pad($prefix, 3, 'X');
        
        // Code canton (2 lettres)
        $cantonCode = strtoupper(substr($canton->nom, 0, 2));
        $cantonCode = preg_replace('/[^A-Z]/', '', $cantonCode);
        $cantonCode = str_pad($cantonCode, 2, 'X');
        
        // Numéro séquentiel
        $numero = self::where('canton_id', $this->canton_id)->count() + 1;
        
        return $cantonCode . '-' . $prefix . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Vérifier si le regroupement peut être supprimé
     */
    public function peutEtreSupprime()
    {
        // Vérifier s'il y a des villages liés (si la table localites existe)
        try {
            $villages_count = \DB::table('localites')
                ->where('regroupement_id', $this->id)
                ->count();
            
            if ($villages_count > 0) {
                return [
                    'peut_supprimer' => false,
                    'raison' => "Ce regroupement contient {$villages_count} village(s). Veuillez d'abord déplacer ou supprimer les villages."
                ];
            }
        } catch (\Exception $e) {
            // Table localites n'existe pas encore
        }
        
        // Vérifier s'il y a des organisations liées (si la table organisations existe)
        try {
            $organisations_count = \DB::table('organisations')
                ->where('regroupement_ref_id', $this->id)
                ->count();
            
            if ($organisations_count > 0) {
                return [
                    'peut_supprimer' => false,
                    'raison' => "Ce regroupement est référencé par {$organisations_count} organisation(s). Veuillez d'abord modifier ces références."
                ];
            }
        } catch (\Exception $e) {
            // Table organisations n'existe pas encore
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
        static::creating(function ($regroupement) {
            if (!$regroupement->code) {
                $regroupement->code = $regroupement->genererCode();
            }
        });
    }
    
    /**
     * Règles de validation
     */
    public static function rules($id = null)
    {
        return [
            'canton_id' => 'required|exists:cantons,id',
            'nom' => [
                'required',
                'string',
                'min:2',
                'max:255',
                $id ? "unique:regroupements,nom,{$id},id,canton_id," . request('canton_id') 
                    : 'unique:regroupements,nom,NULL,id,canton_id,' . request('canton_id')
            ],
            'code' => 'nullable|string|max:30|unique:regroupements,code,' . $id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'ordre_affichage' => 'nullable|integer|min:0'
        ];
    }
}