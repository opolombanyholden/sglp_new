<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentGeneration extends Model
{
    protected $fillable = [
        'document_template_id',
        'dossier_id',
        'dossier_validation_id',
        'organisation_id',
        'numero_document',
        'type_document',
        'qr_code_token',
        'qr_code_url',
        'hash_verification',
        'variables_data',
        'generated_by',
        'generated_at',
        'ip_address',
        'user_agent',
        'download_count',
        'last_downloaded_at',
        'last_downloaded_by',
        'is_valid',
        'invalidated_at',
        'invalidated_by',
        'invalidation_reason',
    ];

    protected $casts = [
        'variables_data' => 'array',
        'generated_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'is_valid' => 'boolean',
    ];

    /**
     * Relations
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function dossierValidation(): BelongsTo
    {
        return $this->belongsTo(DossierValidation::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function lastDownloadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_downloaded_by');
    }

    public function invalidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invalidated_by');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(DocumentVerification::class);
    }

    /**
     * Scopes
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeInvalid($query)
    {
        return $query->where('is_valid', false);
    }

    public function scopeForOrganisation($query, int $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeForDossier($query, int $dossierId)
    {
        return $query->where('dossier_id', $dossierId);
    }

    public function scopeByType($query, string $typeDocument)
    {
        return $query->where('type_document', $typeDocument);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    /**
     * Méthodes
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update([
            'last_downloaded_at' => now(),
            'last_downloaded_by' => auth()->id(),
        ]);
    }

    public function invalidate(string $reason): void
    {
        $this->update([
            'is_valid' => false,
            'invalidated_at' => now(),
            'invalidated_by' => auth()->id(),
            'invalidation_reason' => $reason,
        ]);
    }

    public function verifyHash(array $variables): bool
    {
        $computedHash = hash('sha256', $this->numero_document . json_encode($variables) . config('app.key'));
        return hash_equals($this->hash_verification, $computedHash);
    }

    /**
     * Accesseurs
     */
    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_valid) {
            return '<span class="badge bg-danger">Invalidé</span>';
        }
        return '<span class="badge bg-success">Valide</span>';
    }

    public function getTypeDocumentLabelAttribute(): string
    {
        $types = [
            'recepisse_provisoire' => 'Récépissé provisoire',
            'recepisse_definitif' => 'Récépissé définitif',
            'certificat_enregistrement' => 'Certificat d\'enregistrement',
            'attestation' => 'Attestation',
            'notification_rejet' => 'Notification de rejet',
            'autre' => 'Autre',
        ];

        return $types[$this->type_document] ?? $this->type_document;
    }
}