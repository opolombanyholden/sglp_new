<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DocumentType extends Model
{
    use HasFactory;

    // ✅ FILLABLE - Colonnes après migration
    protected $fillable = [
        'code',
        'libelle',
        'description',
        'is_active',
        'ordre',
        'format_accepte',      // CSV des formats (ex: 'pdf,jpg,png')
        'taille_max',          // En Mo (integer)
    ];

    // ✅ CASTS
    protected $casts = [
        'is_active' => 'boolean',
        'ordre' => 'integer',
        'taille_max' => 'integer',
    ];

    /**
     * ✅ BOOT METHOD
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documentType) {
            // Générer un code unique si non fourni
            if (empty($documentType->code)) {
                $documentType->code = self::generateUniqueCode($documentType->libelle);
            }

            // Définir l'ordre si non fourni
            if (is_null($documentType->ordre)) {
                $maxOrdre = self::max('ordre') ?? 0;
                $documentType->ordre = $maxOrdre + 1;
            }

            // Valeur par défaut pour taille_max (5 Mo)
            if (is_null($documentType->taille_max)) {
                $documentType->taille_max = 5;
            }

            // Format par défaut
            if (empty($documentType->format_accepte)) {
                $documentType->format_accepte = 'pdf,jpg,png';
            }
        });
    }

    /**
     * ✅ RELATIONS
     */
    
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'document_type_id');
    }

    public function template(): HasOne
    {
        return $this->hasOne(DocumentTemplate::class, 'document_type_id');
    }

    /**
     * Relation Many-to-Many avec OrganisationType
     */
    public function organisationTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganisationType::class,
            'document_type_organisation_type',
            'document_type_id',
            'organisation_type_id'
        )->withPivot([
            'is_obligatoire',
            'ordre'
        ])->withTimestamps();
    }

    /**
     * Relation Many-to-Many avec OperationType
     */
    public function operationTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            OperationType::class,
            'document_type_operation_type',
            'document_type_id',
            'operation_type_id'
        )->withPivot([
            'is_obligatoire',
            'ordre'
        ])->withTimestamps();
    }

    /**
     * ✅ SCOPES
     */
    
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactifs($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre', 'asc');
    }

    public function scopeRecherche($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope pour filtrer par type d'organisation
     */
    public function scopeForOrganisationType($query, $organisationTypeId)
    {
        return $query->whereHas('organisationTypes', function($q) use ($organisationTypeId) {
            $q->where('organisation_types.id', $organisationTypeId);
        });
    }

    /**
     * Scope pour filtrer par type d'opération
     */
    public function scopeForOperationType($query, $operationTypeId)
    {
        return $query->whereHas('operationTypes', function($q) use ($operationTypeId) {
            $q->where('operation_types.id', $operationTypeId);
        });
    }

    /**
     * ✅ ACCESSEURS
     */
    
    /**
     * Alias pour compatibilité : nom → libelle
     */
    public function getNomAttribute(): string
    {
        return $this->libelle;
    }

    /**
     * Mutateur pour compatibilité : nom → libelle
     */
    public function setNomAttribute($value)
    {
        $this->attributes['libelle'] = $value;
    }

    /**
     * Obtenir la taille max en octets
     */
    public function getTailleMaxOctetsAttribute(): int
    {
        return $this->taille_max * 1024 * 1024;
    }

    /**
     * Obtenir la taille max lisible
     */
    public function getTailleMaxLisibleAttribute(): string
    {
        $mb = $this->taille_max;
        
        if ($mb < 1) {
            return ($mb * 1024) . ' Ko';
        }
        
        return $mb . ' Mo';
    }

    /**
     * Obtenir les extensions autorisées sous forme de tableau
     */

    /**
     * Obtenir les extensions autorisées sous forme de tableau
     */
    public function getExtensionsAutoriseesAttribute(): array
    {
        if (empty($this->format_accepte)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->format_accepte));
    }

    /**
     * Obtenir les MIME types autorisés
     */
    public function getMimeTypesAutoriseesAttribute(): array
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $types = [];
        foreach ($this->extensions_autorisees as $ext) {
            if (isset($mimeTypes[$ext])) {
                $types[] = $mimeTypes[$ext];
            }
        }

        return array_unique($types);
    }

    /**
     * Statut label
     */
    public function getStatutLabelAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    /**
     * Statut color
     */
    public function getStatutColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Statut badge
     */
    public function getStatutBadgeAttribute(): string
    {
        $class = $this->is_active ? 'badge-success' : 'badge-secondary';
        $label = $this->statut_label;
        return "<span class=\"badge {$class}\">{$label}</span>";
    }

    /**
     * ✅ MÉTHODES STATIQUES
     */
    
    /**
     * Générer un code unique
     */
    public static function generateUniqueCode(string $libelle): string
    {
        $code = strtolower($libelle);
        $code = preg_replace('/[^a-z0-9]+/', '_', $code);
        $code = trim($code, '_');
        $code = substr($code, 0, 50);

        $baseCode = $code;
        $counter = 1;

        while (self::where('code', $code)->exists()) {
            $code = $baseCode . '_' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * ✅ MÉTHODES UTILITAIRES
     */
    
    /**
     * Vérifier si une extension est autorisée
     */
    public function isExtensionAllowed(string $extension): bool
    {
        $extension = strtolower(trim($extension, '.'));
        return in_array($extension, $this->extensions_autorisees);
    }

    /**
     * Vérifier si un MIME type est autorisé
     */
    public function isMimeTypeAllowed(string $mimeType): bool
    {
        return in_array($mimeType, $this->mime_types_autorisees);
    }

    /**
     * Vérifier si une taille de fichier est acceptable
     */
    public function isSizeAllowed(int $sizeInBytes): bool
    {
        return $sizeInBytes <= $this->taille_max_octets;
    }

    /**
     * Valider un fichier uploadé
     */
    public function validateFile($file): array
    {
        $errors = [];

        // Vérifier l'extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$this->isExtensionAllowed($extension)) {
            $errors[] = "Extension '{$extension}' non autorisée. Extensions acceptées : " . $this->extensions_string;
        }

        // Vérifier le MIME type
        $mimeType = $file->getMimeType();
        if (!$this->isMimeTypeAllowed($mimeType)) {
            $errors[] = "Type de fichier non autorisé.";
        }

        // Vérifier la taille
        $size = $file->getSize();
        if (!$this->isSizeAllowed($size)) {
            $errors[] = "Fichier trop volumineux. Taille maximale : " . $this->taille_max_lisible;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Activer/Désactiver
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Réordonner
     */
    public static function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $ordre => $id) {
            self::where('id', $id)->update(['ordre' => $ordre + 1]);
        }
    }

    /**
     * Dupliquer
     */
    public function duplicate(): DocumentType
    {
        $newDocumentType = $this->replicate();
        $newDocumentType->code = self::generateUniqueCode($this->libelle . ' (copie)');
        $newDocumentType->libelle = $this->libelle . ' (copie)';
        $newDocumentType->is_active = false;
        $newDocumentType->save();

        // Dupliquer les relations avec types d'organisation
        foreach ($this->organisationTypes as $orgType) {
            $newDocumentType->organisationTypes()->attach($orgType->id, [
                'is_obligatoire' => $orgType->pivot->is_obligatoire ?? false,
                'ordre' => $orgType->pivot->ordre ?? 0
            ]);
        }

        // Dupliquer les relations avec types d'opération
        foreach ($this->operationTypes as $opType) {
            $newDocumentType->operationTypes()->attach($opType->id, [
                'is_obligatoire' => $opType->pivot->is_obligatoire ?? false,
                'ordre' => $opType->pivot->ordre ?? 0
            ]);
        }

        return $newDocumentType;
    }

    /**
     * Statistiques
     */
    public function getStatistics(): array
    {
        return [
            'total_documents' => $this->documents()->count(),
            'types_organisations' => $this->organisationTypes()->count(),
            'types_operations' => $this->operationTypes()->count(),
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Nombre de types d'organisations
     */
    public function getNombreTypesOrganisationsAttribute(): int
    {
        return $this->organisationTypes()->count();
    }

    /**
     * Nombre de types d'opérations
     */
    public function getNombreTypesOperationsAttribute(): int
    {
        return $this->operationTypes()->count();
    }

    /**
     * Pour select/dropdown
     */
    public static function toSelectOptions(): array
    {
        return self::actifs()
            ->ordered()
            ->get()
            ->mapWithKeys(function ($doc) {
                return [$doc->id => $doc->libelle];
            })
            ->toArray();
    }

    /**
     * Obtenir documents pour une combinaison organisation + opération
     */
    public static function getForOrganisationAndOperation($organisationTypeId, $operationTypeId)
    {
        return self::actifs()
            ->whereHas('organisationTypes', function($q) use ($organisationTypeId) {
                $q->where('organisation_types.id', $organisationTypeId);
            })
            ->whereHas('operationTypes', function($q) use ($operationTypeId) {
                $q->where('operation_types.id', $operationTypeId);
            })
            ->ordered()
            ->get();
    }

}