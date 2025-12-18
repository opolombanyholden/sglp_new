<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle MembreBureau
 * 
 * Représente un membre du bureau de l'organisation
 * Ces membres peuvent être affichés sur le récépissé définitif
 */
class MembreBureau extends Model
{
    use HasFactory;

    protected $table = 'membres_bureau';

    protected $fillable = [
        'organisation_id',
        'nip',
        'nom',
        'prenom',
        'fonction',
        'contact',
        'domicile',
        'afficher_recepisse',
        'ordre',
    ];

    protected $casts = [
        'afficher_recepisse' => 'boolean',
        'ordre' => 'integer',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Relation vers l'organisation
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope : Membres à afficher sur le récépissé
     */
    public function scopePourRecepisse($query)
    {
        return $query->where('afficher_recepisse', true)
            ->orderBy('ordre')
            ->limit(3);
    }

    /**
     * Scope : Ordonné par ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    // ========================================
    // ACCESSEURS
    // ========================================

    /**
     * Nom complet du membre
     */
    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    /**
     * Format pour l'affichage sur le récépissé
     */
    public function getForRecepisseAttribute(): array
    {
        return [
            'nom_complet' => $this->nom_complet,
            'fonction' => $this->fonction,
        ];
    }

    // ========================================
    // VALIDATION
    // ========================================

    /**
     * Règles de validation
     */
    public static function validationRules(): array
    {
        return [
            'nip' => 'required|string|max:50',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'fonction' => 'required|string|max:150',
            'contact' => 'nullable|string|max:100',
            'domicile' => 'nullable|string|max:255',
            'afficher_recepisse' => 'boolean',
            'ordre' => 'integer|min:0',
        ];
    }

    /**
     * Vérifier si on peut ajouter un membre pour le récépissé
     */
    public static function canAddForRecepisse(int $organisationId): bool
    {
        $count = self::where('organisation_id', $organisationId)
            ->where('afficher_recepisse', true)
            ->count();

        return $count < 3;
    }

    /**
     * Nombre de membres sélectionnés pour le récépissé
     */
    public static function countForRecepisse(int $organisationId): int
    {
        return self::where('organisation_id', $organisationId)
            ->where('afficher_recepisse', true)
            ->count();
    }
}
