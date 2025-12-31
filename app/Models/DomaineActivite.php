<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomaineActivite extends Model
{
    use HasFactory;

    protected $table = 'domaines_activite';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'is_active',
        'ordre',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ordre' => 'integer',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Organisations ayant ce domaine d'activité
     */
    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'domaine_activite_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Domaines actifs uniquement
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ordonner par ordre puis par nom
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Label formaté pour affichage
     */
    public function getLabelAttribute(): string
    {
        return $this->nom;
    }

    // ========================================
    // STATIC HELPERS
    // ========================================

    /**
     * Liste pour dropdown (id => nom)
     */
    public static function dropdown(): array
    {
        return static::actif()
            ->ordered()
            ->pluck('nom', 'id')
            ->toArray();
    }

    /**
     * Liste pour select avec objets
     */
    public static function forSelect()
    {
        return static::actif()
            ->ordered()
            ->get(['id', 'nom', 'code']);
    }
}
