<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OperationType extends Model
{
    use HasFactory;

    // ✅ FILLABLE
    protected $fillable = [
        'code',
        'libelle',
        'description',
        'is_active',
        'ordre',
    ];

    // ✅ CASTS
    protected $casts = [
        'is_active' => 'boolean',
        'ordre' => 'integer',
    ];

    /**
     * ✅ RELATIONS
     */
    
    /**
     * Types de documents associés à ce type d'opération
     */
    public function documentTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            DocumentType::class,
            'document_type_operation_type',
            'operation_type_id',
            'document_type_id'
        )->withPivot([
            'is_obligatoire',
            'ordre'
        ])->withTimestamps();
    }

    /**
     * ✅ SCOPES
     */
    
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre', 'asc');
    }

    /**
     * ✅ MÉTHODES STATIQUES
     */
    
    /**
     * Obtenir toutes les options pour select
     */
    public static function toSelectOptions(): array
    {
        return self::actif()
            ->ordered()
            ->get()
            ->mapWithKeys(function ($type) {
                return [$type->code => $type->libelle];
            })
            ->toArray();
    }

    /**
     * ✅ ACCESSEURS
     */
    
    public function getStatutLabelAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getStatutColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }
}