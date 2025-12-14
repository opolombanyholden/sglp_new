<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personnalisations des en-têtes et signatures pour les documents générés
 * 
 * @property int $id
 * @property int $dossier_id
 * @property int $document_template_id
 * @property string|null $header_text
 * @property string|null $signature_text
 * @property int|null $customized_by
 * @property string|null $customized_at
 */
class DocumentGenerationCustomization extends Model
{
    use HasFactory;

    protected $table = 'document_generation_customizations';

    protected $fillable = [
        'dossier_id',
        'document_template_id',
        'header_text',
        'signature_text',
        'customized_by',
        'customized_at',
    ];

    protected $casts = [
        'customized_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: Dossier concerné
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Relation: Template de document
     */
    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    /**
     * Relation: Utilisateur ayant personnalisé
     */
    public function customizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customized_by');
    }

    /**
     * Obtenir le texte d'en-tête (personnalisé ou par défaut du template)
     */
    public function getHeaderTextOrDefault(): ?string
    {
        return $this->header_text ?? $this->documentTemplate->header_text;
    }

    /**
     * Obtenir le texte de signature (personnalisé ou par défaut du template)
     */
    public function getSignatureTextOrDefault(): ?string
    {
        return $this->signature_text ?? $this->documentTemplate->signature_text;
    }
}
