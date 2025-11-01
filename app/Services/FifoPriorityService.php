<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\User;
use App\Models\DossierPriorityHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * ✅ SERVICE DE GESTION FIFO + PRIORITÉ
 * 
 * Gère l'ordre de traitement des dossiers selon la méthode FIFO
 * avec possibilité de priorisation urgente par les administrateurs
 * 
 * Fichier: app/Services/FifoPriorityService.php
 */
class FifoPriorityService
{
    /**
     * Niveaux de priorité avec leur poids pour le tri
     */
    const PRIORITY_WEIGHTS = [
        'urgente' => 0,  // Traitement immédiat
        'haute' => 1,    // Haute priorité
        'moyenne' => 2,  // Priorité moyenne
        'normale' => 3   // FIFO standard
    ];
    
    /**
     * Statuts qui utilisent la queue FIFO
     */
    const FIFO_STATUSES = ['soumis', 'en_cours', 'en_attente'];

    /**
     * ✅ ASSIGNER UN DOSSIER AVEC GESTION DE PRIORITÉ
     */
    public function assignDossierWithPriority(
        Dossier $dossier, 
        User $agent, 
        string $prioriteNiveau = 'normale',
        ?string $justification = null,
        ?string $instructions = null
    ): array {
        
        DB::beginTransaction();
        
        try {
            // Ancien ordre pour l'historique
            $ancienOrdre = $dossier->ordre_traitement;
            $anciennePriorite = $dossier->priorite_niveau ?? 'normale';
            
            // Calculer le nouvel ordre selon la priorité
            $nouvelOrdre = $this->calculateNewOrder($dossier, $prioriteNiveau);
            
            // Mettre à jour le dossier
            $dossier->update([
                'assigned_to' => $agent->id,
                'assigned_at' => now(),
                'assigned_by' => Auth::id(),
                'statut' => 'en_cours',
                'priorite_niveau' => $prioriteNiveau,
                'priorite_urgente' => ($prioriteNiveau === 'urgente'),
                'priorite_justification' => $justification,
                'priorite_assignee_par' => Auth::id(),
                'priorite_assignee_at' => now(),
                'ordre_traitement' => $nouvelOrdre,
                'instructions_agent' => $instructions
            ]);
            
            // Réorganiser les autres dossiers si nécessaire
            if ($prioriteNiveau === 'urgente') {
                $this->reorganizeForUrgentPriority($dossier);
            } elseif ($prioriteNiveau !== $anciennePriorite) {
                $this->reorganizeQueue($dossier->statut);
            }
            
            // Enregistrer l'historique de changement de priorité
            if ($prioriteNiveau !== $anciennePriorite) {
                $this->recordPriorityChange([
                    'dossier_id' => $dossier->id,
                    'ancien_niveau' => $anciennePriorite,
                    'nouveau_niveau' => $prioriteNiveau,
                    'justification' => $justification,
                    'changed_by' => Auth::id(),
                    'ordre_avant' => $ancienOrdre,
                    'ordre_apres' => $nouvelOrdre
                ]);
            }
            
            DB::commit();
            
            Log::info('Dossier assigné avec priorité', [
                'dossier_id' => $dossier->id,
                'agent_id' => $agent->id,
                'priorite' => $prioriteNiveau,
                'ancien_ordre' => $ancienOrdre,
                'nouvel_ordre' => $nouvelOrdre,
                'assigned_by' => Auth::id()
            ]);
            
            return [
                'success' => true,
                'message' => "Dossier assigné à {$agent->name} avec priorité {$prioriteNiveau}",
                'data' => [
                    'ancien_ordre' => $ancienOrdre,
                    'nouvel_ordre' => $nouvelOrdre,
                    'priorite' => $prioriteNiveau,
                    'queue_impacted' => $prioriteNiveau === 'urgente'
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur assignation avec priorité', [
                'dossier_id' => $dossier->id,
                'agent_id' => $agent->id,
                'priorite' => $prioriteNiveau,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * ✅ CALCULER LE NOUVEL ORDRE SELON LA PRIORITÉ
     */
    public function calculateNewOrder(Dossier $dossier, string $prioriteNiveau): int
    {
        switch ($prioriteNiveau) {
            case 'urgente':
                // Position 1 (tête de liste)
                return 1;
                
            case 'haute':
                // Après les urgents, avant les moyens
                $maxUrgentOrder = Dossier::where('statut', $dossier->statut)
                    ->where('priorite_urgente', true)
                    ->max('ordre_traitement') ?? 0;
                return $maxUrgentOrder + 1;
                
            case 'moyenne':
                // Après urgents et hauts, avant normaux
                $maxHighOrder = Dossier::where('statut', $dossier->statut)
                    ->whereIn('priorite_niveau', ['urgente', 'haute'])
                    ->max('ordre_traitement') ?? 0;
                return $maxHighOrder + 1;
                
            default: // 'normale'
                // À la fin de la queue FIFO
                $maxOrder = Dossier::where('statut', $dossier->statut)
                    ->max('ordre_traitement') ?? 0;
                return $maxOrder + 1;
        }
    }
    
    /**
     * ✅ RÉORGANISER POUR PRIORITÉ URGENTE
     */
    private function reorganizeForUrgentPriority(Dossier $urgentDossier): void
    {
        // Décaler tous les autres dossiers du même statut
        Dossier::where('statut', $urgentDossier->statut)
            ->where('id', '!=', $urgentDossier->id)
            ->increment('ordre_traitement');
            
        Log::info('Queue réorganisée pour priorité urgente', [
            'dossier_id' => $urgentDossier->id,
            'statut' => $urgentDossier->statut
        ]);
    }
    
    /**
     * ✅ RÉORGANISER TOUTE LA QUEUE SELON LES PRIORITÉS
     */
    public function reorganizeQueue(string $statut): void
    {
        $dossiers = Dossier::where('statut', $statut)
            ->orderByRaw('
                CASE 
                    WHEN priorite_urgente = 1 THEN 0
                    WHEN priorite_niveau = "haute" THEN 1
                    WHEN priorite_niveau = "moyenne" THEN 2
                    ELSE 3
                END ASC,
                created_at ASC
            ')
            ->get();
            
        foreach ($dossiers as $index => $dossier) {
            $dossier->update(['ordre_traitement' => $index + 1]);
        }
        
        Log::info('Queue complètement réorganisée', [
            'statut' => $statut,
            'nombre_dossiers' => $dossiers->count()
        ]);
    }
    
    /**
     * ✅ OBTENIR LA QUEUE FIFO ORDONNÉE
     */
    public function getOrderedQueue(string $statut, int $limit = 10): array
    {
        $dossiers = Dossier::with(['organisation', 'assignedAgent'])
            ->where('statut', $statut)
            ->orderByRaw('
                CASE 
                    WHEN priorite_urgente = 1 THEN 0
                    WHEN priorite_niveau = "haute" THEN 1
                    WHEN priorite_niveau = "moyenne" THEN 2
                    ELSE 3
                END ASC,
                ordre_traitement ASC,
                created_at ASC
            ')
            ->limit($limit)
            ->get();
            
        return $dossiers->map(function ($dossier, $index) {
            return [
                'id' => $dossier->id,
                'numero_dossier' => $dossier->numero_dossier,
                'organisation_nom' => $dossier->organisation->nom ?? 'N/A',
                'priorite_niveau' => $dossier->priorite_niveau ?? 'normale',
                'priorite_urgente' => $dossier->priorite_urgente ?? false,
                'ordre_actuel' => $index + 1,
                'jours_attente' => now()->diffInDays($dossier->created_at),
                'assigned_to' => $dossier->assignedAgent->name ?? null
            ];
        })->toArray();
    }
    
    /**
     * ✅ CALCULER LA POSITION ESTIMÉE
     */
    public function calculateEstimatedPosition(Dossier $dossier, string $prioriteNiveau): int
    {
        $query = Dossier::where('statut', $dossier->statut)
            ->where('id', '!=', $dossier->id);
            
        switch ($prioriteNiveau) {
            case 'urgente':
                // Compter seulement les autres urgents
                return $query->where('priorite_urgente', true)->count() + 1;
                
            case 'haute':
                // Compter urgents + autres hauts
                return $query->where(function($q) {
                    $q->where('priorite_urgente', true)
                      ->orWhere('priorite_niveau', 'haute');
                })->count() + 1;
                
            case 'moyenne':
                // Compter urgents + hauts + autres moyens
                return $query->whereIn('priorite_niveau', ['urgente', 'haute', 'moyenne'])
                    ->orWhere('priorite_urgente', true)
                    ->count() + 1;
                    
            default: // 'normale'
                // Position en fin de queue
                return Dossier::where('statut', $dossier->statut)->count();
        }
    }
    
    /**
     * ✅ STATISTIQUES DE LA QUEUE
     */
    public function getQueueStatistics(string $statut): array
    {
        $stats = Dossier::where('statut', $statut)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN priorite_urgente = 1 THEN 1 ELSE 0 END) as urgents,
                SUM(CASE WHEN priorite_niveau = "haute" THEN 1 ELSE 0 END) as hauts,
                SUM(CASE WHEN priorite_niveau = "moyenne" THEN 1 ELSE 0 END) as moyens,
                SUM(CASE WHEN priorite_niveau = "normale" OR priorite_niveau IS NULL THEN 1 ELSE 0 END) as normaux,
                AVG(DATEDIFF(NOW(), created_at)) as delai_moyen
            ')
            ->first();
            
        return [
            'total' => $stats->total ?? 0,
            'urgents' => $stats->urgents ?? 0,
            'hauts' => $stats->hauts ?? 0,
            'moyens' => $stats->moyens ?? 0,
            'normaux' => $stats->normaux ?? 0,
            'delai_moyen_jours' => round($stats->delai_moyen ?? 0, 1)
        ];
    }
    
    /**
     * ✅ ENREGISTRER L'HISTORIQUE DE CHANGEMENT DE PRIORITÉ
     */
    private function recordPriorityChange(array $data): void
    {
        DossierPriorityHistory::create($data);
        
        Log::info('Changement de priorité enregistré', $data);
    }
    
    /**
     * ✅ VÉRIFIER SI UN UTILISATEUR PEUT ASSIGNER EN URGENCE
     */
    public function canAssignUrgentPriority(User $user): bool
    {
        // Seuls les admins et superviseurs peuvent assigner en urgence
        return in_array($user->role, ['admin', 'superviseur', 'directeur']);
    }
    
    /**
     * ✅ VALIDER LA JUSTIFICATION POUR PRIORITÉ URGENTE
     */
    public function validateUrgentJustification(?string $justification): bool
    {
        return !empty($justification) && strlen(trim($justification)) >= 20;
    }
    
    /**
     * ✅ OBTENIR L'HISTORIQUE DES CHANGEMENTS DE PRIORITÉ
     */
    public function getPriorityHistory(Dossier $dossier): array
    {
        return DossierPriorityHistory::with('changedBy')
            ->where('dossier_id', $dossier->id)
            ->orderBy('changed_at', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'ancien_niveau' => $history->ancien_niveau,
                    'nouveau_niveau' => $history->nouveau_niveau,
                    'justification' => $history->justification,
                    'changed_by' => $history->changedBy->name ?? 'Utilisateur supprimé',
                    'changed_at' => $history->changed_at->format('d/m/Y à H:i'),
                    'ordre_avant' => $history->ordre_avant,
                    'ordre_apres' => $history->ordre_apres
                ];
            })
            ->toArray();
    }
}