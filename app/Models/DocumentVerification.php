<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVerification extends Model
{
    protected $fillable = [
        'document_generation_id',
        'ip_address',
        'user_agent',
        'geolocation',
        'verification_reussie',
        'motif_echec',
        'verified_at',
    ];

    protected $casts = [
        'geolocation' => 'array',
        'verification_reussie' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function documentGeneration(): BelongsTo
    {
        return $this->belongsTo(DocumentGeneration::class);
    }

    /**
     * Scopes
     */
    public function scopeSuccessful($query)
    {
        return $query->where('verification_reussie', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('verification_reussie', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('verified_at', today());
    }
}