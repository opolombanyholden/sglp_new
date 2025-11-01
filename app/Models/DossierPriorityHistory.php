<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ✅ MODÈLE HISTORIQUE DES CHANGEMENTS DE PRIORITÉ
 * 
 * Fichier: app/Models/DossierPriorityHistory.php
 */
class DossierPriorityHistory extends Model
{
    protected $table = 'dossier_priority_history';
    
    public $timestamps = false; // Utilise changed_at au lieu de created_at/updated_at
    
    protected $fillable = [
        'dossier_id',
        'ancien_niveau',
        'nouveau_niveau',
        'justification',
        'changed_by',
        'changed_at',
        'ordre_avant',
        'ordre_apres',
        'ordre_traitement',
        'priorite_urgente',
        'priorite_niveau',
        'priorite_justification', 
        'priorite_assignee_par',
        'priorite_assignee_at',
        'instructions_agent'
    ];

    
    protected $casts = [
        'changed_at' => 'datetime',
        'priorite_urgente' => 'boolean',
        'priorite_assignee_at' => 'datetime'
    ];
    
    /**
     * Relation vers le dossier
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }
    
    /**
     * Relation vers l'utilisateur qui a fait le changement
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
    
    /**
     * Scope pour filtrer par dossier
     */
    public function scopeForDossier($query, $dossierId)
    {
        return $query->where('dossier_id', $dossierId);
    }
    
    /**
     * Scope pour les changements récents
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }
    
    /**
     * Accesseur pour le niveau de priorité formaté
     */
    public function getAncienNiveauFormateAttribute(): string
    {
        return $this->formatPriorityLevel($this->ancien_niveau);
    }
    
    public function getNouveauNiveauFormateAttribute(): string
    {
        return $this->formatPriorityLevel($this->nouveau_niveau);
    }
    
    /**
     * Formater le niveau de priorité pour l'affichage
     */
    private function formatPriorityLevel(string $niveau): string
    {
        $labels = [
            'normale' => '📋 Normale',
            'moyenne' => '⚠️ Moyenne', 
            'haute' => '🔥 Haute',
            'urgente' => '🚨 Urgente'
        ];
        
        return $labels[$niveau] ?? ucfirst($niveau);
    }
    
    /**
     * Obtenir la différence d'ordre
     */
    public function getDifferenceOrdreAttribute(): ?int
    {
        if ($this->ordre_avant && $this->ordre_apres) {
            return $this->ordre_apres - $this->ordre_avant;
        }
        
        return null;
    }
    
    /**
     * Vérifier si c'est une montée en priorité
     */
    public function getIsUpgradeAttribute(): bool
    {
        $priorities = ['normale' => 3, 'moyenne' => 2, 'haute' => 1, 'urgente' => 0];
        
        return ($priorities[$this->nouveau_niveau] ?? 3) < ($priorities[$this->ancien_niveau] ?? 3);
    }
    
    /**
     * Obtenir la couleur du badge selon le type de changement
     */
    public function getBadgeColorAttribute(): string
    {
        if ($this->is_upgrade) {
            return 'success'; // Montée en priorité = vert
        } elseif ($this->nouveau_niveau === 'urgente') {
            return 'danger'; // Urgente = rouge
        } else {
            return 'warning'; // Autres changements = orange
        }
    }

    // ========== RELATIONS AJOUTÉES ==========

    /**
     * Relation vers l'historique des changements de priorité
     */
    public function priorityHistory()
    {
        return $this->hasMany(DossierPriorityHistory::class);
    }

    /**
     * Relation vers l'utilisateur qui a assigné la priorité
     */
    public function priorityAssignedBy()
    {
        return $this->belongsTo(User::class, 'priorite_assignee_par');
    }

    // ========== SCOPES FIFO ==========

    /**
     * Scope pour l'ordre FIFO complet
     */
    public function scopeOrderedByFifo($query)
    {
        return $query->orderByRaw('
            CASE 
                WHEN priorite_urgente = 1 THEN 0
                WHEN priorite_niveau = "haute" THEN 1
                WHEN priorite_niveau = "moyenne" THEN 2
                ELSE 3
            END ASC,
            ordre_traitement ASC,
            created_at ASC
        ');
    }

    /**
     * Scope pour les dossiers urgents
     */
    public function scopeUrgent($query)
    {
        return $query->where('priorite_urgente', true);
    }

    /**
     * Scope par niveau de priorité
     */
    public function scopeByPriority($query, string $niveau)
    {
        return $query->where('priorite_niveau', $niveau);
    }

    /**
     * Scope pour les dossiers dans la queue FIFO
     */
    public function scopeInFifoQueue($query)
    {
        return $query->whereIn('statut', ['soumis', 'en_cours', 'en_attente']);
    }

    // ========== ACCESSEURS FIFO ==========

    /**
     * Obtenir la priorité formatée pour l'affichage
     */
    public function getPrioriteFormattedAttribute(): string
    {
        $labels = [
            'normale' => '📋 Normale',
            'moyenne' => '⚠️ Moyenne',
            'haute' => '🔥 Haute', 
            'urgente' => '🚨 URGENTE'
        ];
        
        $niveau = $this->priorite_niveau ?? 'normale';
        return $labels[$niveau] ?? ucfirst($niveau);
    }

    /**
     * Obtenir la classe CSS selon la priorité
     */
    public function getPriorityCssClassAttribute(): string
    {
        if ($this->priorite_urgente) {
            return 'badge-danger';
        }
        
        $classes = [
            'haute' => 'badge-warning',
            'moyenne' => 'badge-info', 
            'normale' => 'badge-secondary'
        ];
        
        return $classes[$this->priorite_niveau ?? 'normale'] ?? 'badge-secondary';
    }

    /**
     * Vérifier si le dossier est en priorité
     */
    public function getIsPriorityAttribute(): bool
    {
        return $this->priorite_urgente || in_array($this->priorite_niveau, ['haute', 'moyenne']);
    }

    /**
     * Obtenir la position dans la queue
     */
    public function getPositionInQueueAttribute(): int
    {
        return self::where('statut', $this->statut)
            ->where(function($query) {
                $query->where('priorite_urgente', true)
                    ->orWhere('priorite_niveau', 'haute')
                    ->orWhere('priorite_niveau', 'moyenne')
                    ->orWhere('ordre_traitement', '<', $this->ordre_traitement);
            })
            ->count() + 1;
    }

    /**
     * Obtenir les jours d'attente
     */
    public function getJoursAttenteAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Vérifier si le dossier est en retard
     */
    public function getIsLateAttribute(): bool
    {
        // Considéré en retard si :
        // - Urgente et > 1 jour
        // - Haute priorité et > 3 jours  
        // - Normale et > 7 jours
        $jours = $this->jours_attente;
        
        if ($this->priorite_urgente) {
            return $jours > 1;
        } elseif ($this->priorite_niveau === 'haute') {
            return $jours > 3;
        } elseif ($this->priorite_niveau === 'moyenne') {
            return $jours > 5;
        } else {
            return $jours > 7;
        }
    }

    // ========== MÉTHODES FIFO ==========

    /**
     * Déplacer le dossier en tête de queue (priorité urgente)
     */
    public function moveToTop(?string $justification = null): bool
    {
        DB::beginTransaction();
        
        try {
            // Enregistrer l'ancien ordre
            $ancienOrdre = $this->ordre_traitement;
            
            // Décaler tous les autres dossiers
            self::where('statut', $this->statut)
                ->where('id', '!=', $this->id)
                ->increment('ordre_traitement');
            
            // Mettre ce dossier en position 1
            $this->update([
                'ordre_traitement' => 1,
                'priorite_urgente' => true,
                'priorite_niveau' => 'urgente',
                'priorite_justification' => $justification,
                'priorite_assignee_par' => auth()->id(),
                'priorite_assignee_at' => now()
            ]);
            
            // Enregistrer dans l'historique
            $this->priorityHistory()->create([
                'ancien_niveau' => $this->getOriginal('priorite_niveau') ?? 'normale',
                'nouveau_niveau' => 'urgente',
                'justification' => $justification,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'ordre_avant' => $ancienOrdre,
                'ordre_apres' => 1
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Changer la priorité du dossier
     */
    public function changePriority(string $nouveauNiveau, ?string $justification = null): bool
    {
        if ($this->priorite_niveau === $nouveauNiveau) {
            return true; // Pas de changement
        }
        
        DB::beginTransaction();
        
        try {
            $ancienNiveau = $this->priorite_niveau ?? 'normale';
            $ancienOrdre = $this->ordre_traitement;
            
            // Calculer le nouvel ordre
            $fifoService = app(FifoPriorityService::class);
            $nouvelOrdre = $fifoService->calculateNewOrder($this, $nouveauNiveau);
            
            // Mettre à jour le dossier
            $this->update([
                'priorite_niveau' => $nouveauNiveau,
                'priorite_urgente' => ($nouveauNiveau === 'urgente'),
                'priorite_justification' => $justification,
                'priorite_assignee_par' => auth()->id(),
                'priorite_assignee_at' => now(),
                'ordre_traitement' => $nouvelOrdre
            ]);
            
            // Réorganiser si nécessaire
            if ($nouveauNiveau === 'urgente') {
                $fifoService->reorganizeForUrgentPriority($this);
            } else {
                $fifoService->reorganizeQueue($this->statut);
            }
            
            // Historique
            $this->priorityHistory()->create([
                'ancien_niveau' => $ancienNiveau,
                'nouveau_niveau' => $nouveauNiveau,
                'justification' => $justification,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'ordre_avant' => $ancienOrdre,
                'ordre_apres' => $nouvelOrdre
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtenir les dossiers suivants dans la queue
     */
    public function getNextInQueue(int $limit = 5)
    {
        return self::where('statut', $this->statut)
            ->where('ordre_traitement', '>', $this->ordre_traitement)
            ->orderedByFifo()
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les dossiers précédents dans la queue  
     */
    public function getPreviousInQueue(int $limit = 5)
    {
        return self::where('statut', $this->statut)
            ->where('ordre_traitement', '<', $this->ordre_traitement)
            ->orderedByFifo()
            ->orderBy('ordre_traitement', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculer le délai de traitement estimé
     */
    public function getEstimatedProcessingTimeAttribute(): array
    {
        $position = $this->position_in_queue;
        
        // Temps moyen par dossier selon la priorité (en heures)
        $tempsParDossier = [
            'urgente' => 4,   // 4h pour traiter un dossier urgent
            'haute' => 8,     // 8h pour haute priorité
            'moyenne' => 16,  // 16h pour moyenne
            'normale' => 24   // 24h pour normale
        ];
        
        $temps = $tempsParDossier[$this->priorite_niveau ?? 'normale'];
        $heuresEstimees = $position * $temps;
        
        return [
            'heures' => $heuresEstimees,
            'jours' => ceil($heuresEstimees / 8), // 8h de travail par jour
            'date_estimee' => now()->addHours($heuresEstimees)->format('d/m/Y')
        ];
    }
}